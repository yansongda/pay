<?php

declare(strict_types=1);

namespace Yansongda\Pay\Crypto\Apple;

use DateTime;
use DateTimeZone;
use OpenSSLAsymmetricKey;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;

/**
 * Apple Pay 支付令牌（`payToken`）本地验签与解密。
 *
 * PKCS#7 证书链验签（signedAttrs/messageDigest/signingTime + 商户公钥哈希绑定）
 * → ECDH + KDF（EC_v1）/ RSA-OAEP（RSA_v1）→ AES-256/128-GCM 解密。
 *
 * 通用密码学工具见 Cryptor，通知域（JWS）见 JwsVerifier。
 */
final class TokenVerifier
{
    /* Apple 证书扩展 OID：支付令牌链（PKCS#7） */
    private const TOKEN_INTERMEDIATE_OID = '1.2.840.113635.100.6.2.14';
    private const TOKEN_LEAF_OID = '1.2.840.113635.100.6.29';

    /* PKCS#7 SignedAttributes 属性 OID */
    private const OID_MESSAGE_DIGEST = '1.2.840.113549.1.9.4';
    private const OID_SIGNING_TIME = '1.2.840.113549.1.9.5';

    /** signingTime 允许的时钟偏差窗口（秒） */
    private const SIGNING_TIME_WINDOW = 300;

    /** PKCS#7 签名 DER 的最大长度（字节），真实 Apple token 约 3-4KB，超出直接拒绝 */
    private const PKCS7_MAX_LENGTH = 65536;

    private function __construct() {}

    /**
     * 验签并解密支付令牌，返回解密后的数据（含 `_token_header`）。
     *
     * @param array<string, mixed> $token
     *
     * @return array<string, mixed>
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     */
    public static function verify(AppleConfig $config, array $token): array
    {
        if (!isset($token['data'], $token['header'], $token['signature'], $token['version'])
            || !is_string($token['data'])
            || !is_array($token['header'])
            || !is_string($token['signature'])
            || !is_string($token['version'])
        ) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: Apple 支付令牌结构非法');
        }

        $header = $token['header'];

        // transactionId：官方口径为十六进制字符串（etsy applepay.c 的 base64 处理属实现分歧，不做回退）
        $transactionId = Cryptor::hexToBinSafe(
            (string) ($header['transactionId'] ?? ''),
            Exception::PARAMS_APPLE_TOKEN_INVALID,
            '参数异常: Apple 支付令牌 `header.transactionId` 非法'
        );

        $applicationData = '';
        if (isset($header['applicationData']) && '' !== $header['applicationData']) {
            // applicationData：官方口径为十六进制字符串
            $applicationData = Cryptor::hexToBinSafe(
                (string) $header['applicationData'],
                Exception::PARAMS_APPLE_TOKEN_INVALID,
                '参数异常: Apple 支付令牌 `header.applicationData` 非法'
            );
        }

        if ('EC_v1' === $token['version']) {
            $signingKeyName = 'ephemeralPublicKey';
        } elseif ('RSA_v1' === $token['version']) {
            self::assertRsaV1Supported();

            $signingKeyName = 'wrappedKey';
        } else {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: Apple 支付令牌 `version` 非法');
        }

        if (!isset($header[$signingKeyName]) || !is_string($header[$signingKeyName]) || '' === $header[$signingKeyName]) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: Apple 支付令牌缺少签名字段 -- ['.$signingKeyName.']');
        }

        if (!isset($header['publicKeyHash']) || !is_string($header['publicKeyHash']) || '' === $header['publicKeyHash']) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: Apple 支付令牌缺少签名字段 -- [publicKeyHash]');
        }

        $signingKey = base64_decode($header[$signingKeyName], true);
        $data = base64_decode($token['data'], true);

        if (false === $signingKey || false === $data) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: Apple 支付令牌 `data` 非法');
        }

        $signedContent = $signingKey.$data.$transactionId.$applicationData;

        self::verifyPkcs7Signature($config, $token['signature'], $signedContent, $header);

        $merchantPrivateKey = openssl_pkey_get_private(
            CertManager::getPrivateCert($config->getPaymentProcessingCert()),
            $config->getPaymentProcessingCertPassphrase()
        );

        if (false === $merchantPrivateKey) {
            throw new InvalidConfigException(
                Exception::CONFIG_CERT_PARSE_FAILED,
                '配置异常: 解析 Apple 支付处理证书私钥失败，请确认私钥为 PKCS#8 格式（-----BEGIN PRIVATE KEY-----）或配置为文件路径'
            );
        }

        $decrypted = self::decryptData($token['version'], $header, $token['data'], $merchantPrivateKey, $config->getMerchantId());

        return $decrypted + ['_token_header' => $header];
    }

    /**
     * 验证 Apple Pay 支付令牌 PKCS#7 签名与商户绑定（pubkeyHash）。
     *
     * 编排：PKCS#7 解析与证书链 → 商户公钥哈希绑定 → signedAttrs 验签与属性校验。
     *
     * @param array<string, mixed> $header
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     */
    private static function verifyPkcs7Signature(AppleConfig $config, string $signature, string $signedContent, array $header): void
    {
        $p7 = base64_decode($signature, true);

        if (false === $p7 || '' === $p7 || self::PKCS7_MAX_LENGTH < strlen($p7)) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: Apple 支付令牌 `signature` 非法');
        }

        $signer = self::parsePkcs7Signer($p7, $config);

        self::assertMerchantKeyHash($config, $header);
        self::verifySignedAttrs($signer['signedAttrs'], $signer['leafPem'], $signer['signatureValue'], $signedContent);
    }

    /**
     * 解析 PKCS#7（ContentInfo → SignedData → SignerInfo）并验证证书链，返回验签所需物料。
     *
     * @return array{signedAttrs: array<string, mixed>, signatureValue: string, leafPem: string}
     *
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    private static function parsePkcs7Signer(string $p7, AppleConfig $config): array
    {
        // ContentInfo ::= SEQUENCE { contentType OID, content [0] EXPLICIT SignedData }
        $contentInfo = Cryptor::parseAsn1($p7);

        if (0x30 !== $contentInfo['tag']
            || 2 !== count($contentInfo['children'])
            || 0x06 !== $contentInfo['children'][0]['tag']
            || 0xA0 !== $contentInfo['children'][1]['tag']
            || 1 !== count($contentInfo['children'][1]['children'])
            || 0x30 !== $contentInfo['children'][1]['children'][0]['tag']
        ) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌 PKCS#7 结构非法');
        }

        $signedData = $contentInfo['children'][1]['children'][0];

        // SignedData ::= SEQUENCE { version, digestAlgorithms, encapContentInfo,
        //                           certificates [0] IMPLICIT, crls [1] IMPLICIT, signerInfos SET }
        // 定位 certificates：第一个 context-specific tag（class bits & 0xC0 = 0x80，0xA0/0xA1 均命中）
        $certificatesNode = null;

        foreach ($signedData['children'] as $child) {
            if (0x80 === ($child['tag'] & 0xC0)) {
                $certificatesNode = $child;

                break;
            }
        }

        $signerInfosNode = $signedData['children'][count($signedData['children']) - 1] ?? null;

        if (null === $signerInfosNode || 0x31 !== $signerInfosNode['tag'] || [] === $signerInfosNode['children']) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌 PKCS#7 缺少 SignerInfos');
        }

        $signerInfo = $signerInfosNode['children'][0];

        if (0x30 !== $signerInfo['tag']) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌 PKCS#7 SignerInfo 非法');
        }

        // SignerInfo ::= SEQUENCE { version, sid, digestAlgorithm, [0] signedAttrs,
        //                          signatureAlgorithm, signature, [1] unsignedAttrs }
        $signedAttrsNode = null;
        $signatureValue = null;

        foreach ($signerInfo['children'] as $child) {
            if (0xA0 === $child['tag'] && null === $signedAttrsNode) {
                $signedAttrsNode = $child;
            }

            if (0x04 === $child['tag']) {
                $signatureValue = $child['value'];
            }
        }

        if (null === $signedAttrsNode || null === $signatureValue || '' === $signatureValue) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌 PKCS#7 缺少签名属性或签名值');
        }

        if (null === $certificatesNode) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌 PKCS#7 缺少证书');
        }

        // 证书 DER → PEM；配置兜底的 intermediate 一并参与 OID 定位（PKCS#7 只含 leaf 时兜底）
        $certs = [];

        foreach ($certificatesNode['children'] as $certNode) {
            if (0x30 === $certNode['tag']) {
                $certs[] = Cryptor::pemWrap(Cryptor::reConstructDer($certNode), 'CERTIFICATE');
            }
        }

        if ([] === $certs) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌 PKCS#7 缺少证书');
        }

        $rootPem = CertManager::getPublicCert($config->getAppleRootCa());

        // 中间证书兜底：PKCS#7 报文缺少 intermediate 时使用内置/配置的中间 CA
        $certs[] = CertManager::getPublicCert($config->getAppleIntermediateCa());

        $leafPem = Cryptor::verifyChain($certs, $rootPem, self::TOKEN_INTERMEDIATE_OID, self::TOKEN_LEAF_OID);

        return ['signedAttrs' => $signedAttrsNode, 'signatureValue' => $signatureValue, 'leafPem' => $leafPem];
    }

    /**
     * 商户绑定校验：sha256(商户证书 SPKI DER) === header.publicKeyHash。
     *
     * @param array<string, mixed> $header
     *
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    private static function assertMerchantKeyHash(AppleConfig $config, array $header): void
    {
        $merchantPublicKey = openssl_pkey_get_public(CertManager::getPublicCert($config->getPaymentProcessingCert()));

        if (false === $merchantPublicKey) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析 Apple 支付处理证书失败');
        }

        $merchantKeyDetails = openssl_pkey_get_details($merchantPublicKey);
        $merchantSpkiPem = (string) ($merchantKeyDetails['key'] ?? '');

        if ('' === $merchantSpkiPem) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析 Apple 支付处理证书失败');
        }

        $merchantSpkiDer = Cryptor::pemToDer($merchantSpkiPem, '配置异常: 解析 Apple 支付处理证书失败', 'PUBLIC KEY');

        $tokenPublicKeyHash = base64_decode((string) ($header['publicKeyHash'] ?? ''), true);

        if (false === $tokenPublicKeyHash || !hash_equals(hash('sha256', $merchantSpkiDer, true), $tokenPublicKeyHash)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌商户证书哈希不匹配');
        }
    }

    /**
     * 验证 SignedAttributes：签名值、messageDigest 一致性、signingTime 有效期。
     *
     * @param array<string, mixed> $signedAttrsNode
     *
     * @throws InvalidSignException
     */
    private static function verifySignedAttrs(array $signedAttrsNode, string $leafPem, string $signatureValue, string $signedContent): void
    {
        // RFC 5652 §5.4：signature 验证对象为 DER 编码的 SignedAttrs（0xA0 → 0x31）
        $der31 = Cryptor::reConstructDer($signedAttrsNode);

        if ("\xA0" !== $der31[0]) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌签名属性非法');
        }

        $der31 = "\x31".substr($der31, 1);
        $leafPublicKey = openssl_pkey_get_public($leafPem);

        if (false === $leafPublicKey || 1 !== openssl_verify($der31, $signatureValue, $leafPublicKey, OPENSSL_ALGO_SHA256)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌签名验证失败');
        }

        // SignedAttributes 属性遍历：messageDigest + signingTime
        $messageDigest = null;
        $signingTimeNode = null;

        foreach ($signedAttrsNode['children'] as $attribute) {
            if (0x30 !== $attribute['tag'] || 2 > count($attribute['children']) || 0x06 !== $attribute['children'][0]['tag']) {
                continue;
            }

            $oidDer = $attribute['children'][0]['value'];
            $valueNode = $attribute['children'][1];
            $innerValueNode = (0x31 === $valueNode['tag'] && isset($valueNode['children'][0])) ? $valueNode['children'][0] : $valueNode;

            if (Cryptor::encodeAsn1Oid(self::OID_MESSAGE_DIGEST) === $oidDer) {
                if (0x04 === $innerValueNode['tag']) {
                    $messageDigest = $innerValueNode['value'];
                }
            } elseif (Cryptor::encodeAsn1Oid(self::OID_SIGNING_TIME) === $oidDer) {
                if (0x17 === $innerValueNode['tag'] || 0x18 === $innerValueNode['tag']) {
                    $signingTimeNode = $innerValueNode;
                }
            }
        }

        if (null === $messageDigest) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌缺少 messageDigest 属性');
        }

        if (!hash_equals($messageDigest, hash('sha256', $signedContent, true))) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌消息摘要不一致');
        }

        // signingTime 属性缺失同为 SIGN_ERROR（安全优先；真实 token 由 openssl_pkcs7_sign 默认生成含该属性）
        if (null === $signingTimeNode) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌缺少 signingTime 属性');
        }

        $signingDateTime = 0x17 === $signingTimeNode['tag']
            ? DateTime::createFromFormat('ymdHis\Z', $signingTimeNode['value'], new DateTimeZone('UTC'))
            : DateTime::createFromFormat('YmdHis\Z', $signingTimeNode['value'], new DateTimeZone('UTC'));

        if (false === $signingDateTime || abs(time() - $signingDateTime->getTimestamp()) > self::SIGNING_TIME_WINDOW) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌签名时间超出允许范围');
        }
    }

    /**
     * ECDH + KDF 派生 AES-256-GCM 对称密钥（NIST SP 800-56A 计数器模式 KDF）。
     *
     * @throws InvalidConfigException
     */
    private static function deriveSymmetricKey(string $ephemeralPublicKey, OpenSSLAsymmetricKey $merchantPrivateKey, string $merchantId): string
    {
        $ephemeralPublicKeyDer = base64_decode($ephemeralPublicKey, true);

        if (false === $ephemeralPublicKeyDer) {
            throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌 `header.ephemeralPublicKey` 非法');
        }

        $sharedSecret = openssl_pkey_derive(Cryptor::pemWrap($ephemeralPublicKeyDer, 'PUBLIC KEY'), $merchantPrivateKey);

        if (false === $sharedSecret) {
            throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌 ECDH 密钥协商失败');
        }

        // KDF（etsy applepay.c / PayU-EMEA Ecc.php 交叉核对）:
        // SHA256(0x00 0x00 0x00 0x01 || Z || 0x0D || "id-aes256-GCM" || "Apple" || SHA256(merchantId))
        return hash(
            'sha256',
            "\x00\x00\x00\x01".$sharedSecret.chr(0x0D).'id-aes256-GCMApple'.hash('sha256', $merchantId, true),
            true
        );
    }

    /**
     * 解密支付令牌 data 字段，返回解密后的 JSON 数组。
     *
     * @param array<string, mixed> $header
     *
     * @return array<string, mixed>
     *
     * @throws InvalidConfigException
     */
    private static function decryptData(string $version, array $header, string $data, OpenSSLAsymmetricKey $merchantPrivateKey, string $merchantId): array
    {
        $raw = base64_decode($data, true);

        if (false === $raw || 16 > strlen($raw)) {
            throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌密文非法');
        }

        // iv 16 字节全零；tag 为密文末尾 16 字节
        $tag = substr($raw, -16);
        $cipherText = substr($raw, 0, -16);

        if ('EC_v1' === $version) {
            $symmetricKey = self::deriveSymmetricKey((string) $header['ephemeralPublicKey'], $merchantPrivateKey, $merchantId);
            $cipher = 'aes-256-gcm';
        } else {
            self::assertRsaV1Supported();

            $wrappedKey = base64_decode((string) $header['wrappedKey'], true);

            if (false === $wrappedKey) {
                throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌 `header.wrappedKey` 非法');
            }

            // PHP 8.5 起 digest_algo 为第 5 位置参数
            /** @var array<int, int|string> $oaepOptions 展开传入以规避 PHPStan 在 < 8.5 存根下的 arguments.count 误报 */
            $oaepOptions = [OPENSSL_PKCS1_OAEP_PADDING, 'sha256'];

            if (!openssl_private_decrypt($wrappedKey, $symmetricKey, $merchantPrivateKey, ...$oaepOptions)) {
                throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌 RSA-OAEP 解包失败');
            }

            $cipher = 'aes-128-gcm';
        }

        $plain = openssl_decrypt($cipherText, $cipher, $symmetricKey, OPENSSL_RAW_DATA, str_repeat("\x00", 16), $tag, '');

        if (false === $plain) {
            throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌 AES-GCM 解密失败');
        }

        $decrypted = json_decode($plain, true);

        if (!is_array($decrypted)) {
            throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌解密结果非法');
        }

        return $decrypted;
    }

    /**
     * RSA_v1 解包依赖 openssl_private_decrypt 的 digest_algo（PHP 8.5 新增，8.2–8.4 硬编码 OAEP SHA-1）。
     *
     * EC_v1 无此要求；不支持的版本在验签/解密前直接拒绝，避免白做 PKCS#7 验签与证书解析。
     *
     * @throws InvalidConfigException
     */
    private static function assertRsaV1Supported(): void
    {
        if (PHP_VERSION_ID < 80500) {
            throw new InvalidConfigException(
                Exception::DECRYPT_APPLE_FAILED,
                '解密异常: Apple RSA_v1 令牌解密需要 PHP >= 8.5（OAEP-SHA256 参数限制），EC_v1 无此要求'
            );
        }
    }
}
