<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use DateTime;
use DateTimeZone;
use OpenSSLAsymmetricKey;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Apple;
use Yansongda\Supports\Collection;

/**
 * Apple Pay 密码学核心。
 *
 * - payToken 支付令牌本地验签解密（PKCS#7 / ECDH-KDF / AES-GCM）
 * - App Store Server API JWT 生成（ES256）
 * - App Store Server Notifications V2 JWS 验签（x5c 证书链）
 */
trait AppleTrait
{
    use ProviderConfigTrait;

    /* Apple 证书扩展 OID：支付令牌链（PKCS#7） */
    private const APPLE_TOKEN_INTERMEDIATE_OID = '1.2.840.113635.100.6.2.14';
    private const APPLE_TOKEN_LEAF_OID = '1.2.840.113635.100.6.29';

    /* Apple 证书扩展 OID：App Store Server Notifications V2（JWS） */
    private const APPLE_JWS_INTERMEDIATE_OID = '1.2.840.113635.100.6.2.1';
    private const APPLE_JWS_LEAF_OID = '1.2.840.113635.100.6.11.1';

    /* PKCS#7 SignedAttributes 属性 OID */
    private const APPLE_OID_MESSAGE_DIGEST = '1.2.840.113549.1.9.4';
    private const APPLE_OID_SIGNING_TIME = '1.2.840.113549.1.9.5';

    /** signingTime 允许的时钟偏差窗口（秒） */
    private const APPLE_SIGNING_TIME_WINDOW = 300;

    /**
     * @throws InvalidParamsException
     */
    public static function getAppleUrl(AppleConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_URL_MISSING, '参数异常: Apple `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Apple::URL[$config->getMode()].$url;
    }

    /**
     * 本地验签解密 Apple Pay 支付令牌。
     *
     * @param array<string, mixed>|Collection|string $token
     * @param array<string, mixed>                   $params
     *
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public static function verifyAppleToken(array|Collection|string $token, array $params = []): Collection
    {
        /** @var AppleConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_APPLE, $params);

        if (is_string($token)) {
            $token = json_decode($token, true);
        } elseif ($token instanceof Collection) {
            $token = $token->toArray();
        }

        if (!is_array($token)
            || !isset($token['data'], $token['header'], $token['signature'], $token['version'])
            || !is_string($token['data'])
            || !is_array($token['header'])
            || !is_string($token['signature'])
            || !is_string($token['version'])
        ) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: Apple 支付令牌结构非法');
        }

        $header = $token['header'];

        // transactionId：官方口径为十六进制字符串（etsy applepay.c 的 base64 处理属实现分歧，不做回退）
        $transactionId = self::hexToBinSafe(
            (string) ($header['transactionId'] ?? ''),
            Exception::PARAMS_APPLE_TOKEN_INVALID,
            '参数异常: Apple 支付令牌 `header.transactionId` 非法'
        );

        $applicationData = '';
        if (isset($header['applicationData']) && '' !== $header['applicationData']) {
            // applicationData：官方口径为十六进制字符串
            $applicationData = self::hexToBinSafe(
                (string) $header['applicationData'],
                Exception::PARAMS_APPLE_TOKEN_INVALID,
                '参数异常: Apple 支付令牌 `header.applicationData` 非法'
            );
        }

        if ('EC_v1' === $token['version']) {
            $signingKeyName = 'ephemeralPublicKey';
        } elseif ('RSA_v1' === $token['version']) {
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

        self::verifyAppleTokenSignature($config, $token['signature'], $signedContent, $header);

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

        $decrypted = self::decryptAppleData($token['version'], $header, $token['data'], $merchantPrivateKey, $config->getMerchantId());

        return new Collection($decrypted + ['_token_header' => $header]);
    }

    /**
     * 验证 App Store Server Notifications V2 JWS 签名。
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public static function verifyAppleJws(string $signedPayload, array $params = []): array
    {
        /** @var AppleConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_APPLE, $params);
        $rootCa = $config->getAppleRootCa();

        if (empty($rootCa)) {
            throw new InvalidConfigException(Exception::CONFIG_APPLE_INVALID, '配置异常: Apple JWS 验签需要配置 -- [apple_root_ca]');
        }

        $rootDer = self::certPemToDer(CertManager::getPublicCert($rootCa));

        $verified = self::verifyAppleJwsNode($signedPayload, $rootDer, null);
        $payload = $verified['payload'];

        if (isset($payload['data']) && is_array($payload['data'])) {
            foreach (['signedTransactionInfo', 'signedRenewalInfo'] as $key) {
                if (isset($payload['data'][$key])) {
                    $payload['data'][$key] = self::verifyAppleJwsNode(
                        (string) $payload['data'][$key],
                        $rootDer,
                        $verified['leafPem']
                    )['payload'];
                }
            }
        }

        return $payload;
    }

    /**
     * 生成 App Store Server API 请求 JWT（ES256）。
     *
     * @param array<string, mixed> $params
     *
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public static function generateAppleJwt(array $params = []): string
    {
        /** @var AppleConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_APPLE, $params);

        $issuerId = $config->getIssuerId();
        $bundleId = $config->getBundleId();
        $apiKeyId = $config->getApiKeyId();
        $apiPrivateKey = $config->getApiPrivateKey();

        if (empty($issuerId) || empty($bundleId) || empty($apiKeyId) || empty($apiPrivateKey)) {
            throw new InvalidConfigException(
                Exception::CONFIG_APPLE_INVALID,
                '配置异常: Apple App Store Server API 配置不完整 -- [issuer_id]/[bundle_id]/[api_key_id]/[api_private_key] 需同时配置'
            );
        }

        $privateKey = openssl_pkey_get_private(CertManager::getPrivateCert((string) $apiPrivateKey));

        if (false === $privateKey) {
            throw new InvalidConfigException(Exception::CONFIG_APPLE_INVALID, '配置异常: 解析 Apple App Store Server API 私钥失败 -- [api_private_key]');
        }

        $header = ['alg' => 'ES256', 'kid' => $apiKeyId, 'typ' => 'JWT'];
        $now = time();
        $payload = [
            'iss' => $issuerId,
            'iat' => $now,
            'exp' => $now + self::APPLE_SIGNING_TIME_WINDOW,
            'aud' => 'appstoreconnect-v1',
            'bid' => $bundleId,
        ];

        $headerB64 = self::base64UrlEncode((string) json_encode($header));
        $payloadB64 = self::base64UrlEncode((string) json_encode($payload));

        if (!openssl_sign($headerB64.'.'.$payloadB64, $derSignature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new InvalidConfigException(Exception::CONFIG_APPLE_INVALID, '配置异常: Apple App Store Server API JWT 签名失败 -- [api_private_key]');
        }

        return $headerB64.'.'.$payloadB64.'.'.self::base64UrlEncode(self::derToRawApple($derSignature));
    }

    /**
     * 解析 ASN.1 DER 节点（PKCS#7 专用）。
     *
     * 返回节点的 tag、原始内容切片与子节点；子节点内容为原 DER 的完整切片，
     * 因此 reConstructDer() 可自包含地重建节点完整 DER（规避相对 offset 错位）。
     *
     * @return array{tag: int, value: string, children: array<int, array{tag: int, value: string, children: array<int, mixed>}>}
     */
    private static function parseAppleAsn1(string $der, int &$offset = 0): array
    {
        $tag = ord($der[$offset++]);
        $lengthByte = ord($der[$offset++]);
        $length = $lengthByte;

        if (0 !== ($lengthByte & 0x80)) {
            $bytesCount = $lengthByte & 0x7F;
            $length = 0;

            for ($i = 0; $i < $bytesCount; ++$i) {
                $length = ($length << 8) | ord($der[$offset++]);
            }
        }

        $value = substr($der, $offset, $length);
        $offset += $length;

        $children = [];

        if (0 !== ($tag & 0x20)) {
            $childOffset = 0;
            $valueLength = strlen($value);

            while ($childOffset < $valueLength) {
                $children[] = self::parseAppleAsn1($value, $childOffset);
            }
        }

        return ['tag' => $tag, 'value' => $value, 'children' => $children];
    }

    /**
     * 还原节点的完整 DER 编码（tag + length + value）。
     *
     * @param array{tag: int, value: string, children: array<int, array{tag: int, value: string, children: array<int, mixed>}>} $node
     */
    private static function reConstructDer(array $node): string
    {
        if ([] !== $node['children']) {
            $parts = [];

            foreach ($node['children'] as $child) {
                $parts[] = self::reConstructDer($child);
            }

            $node['value'] = implode('', $parts);
        }

        return chr($node['tag']).self::asn1LengthEncode(strlen($node['value'])).$node['value'];
    }

    private static function asn1LengthEncode(int $length): string
    {
        if (0x80 > $length) {
            return chr($length);
        }

        $encoded = '';

        while (0 < $length) {
            $encoded = chr($length & 0xFF).$encoded;
            $length >>= 8;
        }

        return chr(0x80 | strlen($encoded)).$encoded;
    }

    /**
     * DER 内容包装为 PEM（如 label 取 CERTIFICATE / PUBLIC KEY）。
     */
    private static function pemWrap(string $der, string $label): string
    {
        return '-----BEGIN '.$label.'-----'."\n".chunk_split(base64_encode($der), 64, "\n").'-----END '.$label.'-----'."\n";
    }

    /**
     * 从 PEM 提取证书 DER。
     */
    private static function certPemToDer(string $pem): string
    {
        $lines = preg_split('/\r?\n/', $pem);

        if (false === $lines) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析 Apple 根证书失败');
        }

        $base64 = '';
        $inBlock = false;

        foreach ($lines as $line) {
            if ('-----BEGIN CERTIFICATE-----' === $line) {
                $inBlock = true;

                continue;
            }

            if ('-----END CERTIFICATE-----' === $line) {
                break;
            }

            if ($inBlock) {
                $base64 .= trim($line);
            }
        }

        $der = base64_decode($base64, true);

        if (false === $der || '' === $der) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析 Apple 根证书失败');
        }

        return $der;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @throws InvalidSignException
     */
    private static function base64UrlDecode(string $data): string
    {
        $padding = (4 - strlen($data) % 4) % 4;
        $decoded = base64_decode(strtr($data, '-_', '+/').str_repeat('=', $padding), true);

        if (false === $decoded) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS base64url 解码失败');
        }

        return $decoded;
    }

    /**
     * 十六进制字符串安全解码（防 PHP 8 hex2bin() ValueError）。
     *
     * @throws InvalidParamsException
     */
    private static function hexToBinSafe(string $hex, int $errorCode, string $message): string
    {
        if ('' === $hex || !ctype_xdigit($hex) || 0 !== strlen($hex) % 2) {
            throw new InvalidParamsException($errorCode, $message);
        }

        $bin = hex2bin($hex);

        if (false === $bin) {
            throw new InvalidParamsException($errorCode, $message);
        }

        return $bin;
    }

    /**
     * 把点分 OID 编码为 DER 内容字节，用于与 PKCS#7 属性 OID 直接比较。
     */
    private static function encodeAsn1Oid(string $oid): string
    {
        $parts = array_map('intval', explode('.', $oid));
        $first = array_shift($parts);
        $second = array_shift($parts) ?? 0;
        $der = chr($first * 40 + $second);

        foreach ($parts as $part) {
            $encoded = chr($part & 0x7F);
            $part >>= 7;

            while (0 < $part) {
                $encoded = chr(0x80 | ($part & 0x7F)).$encoded;
                $part >>= 7;
            }

            $der .= $encoded;
        }

        return $der;
    }

    /**
     * 验证 Apple Pay 支付令牌 PKCS#7 签名与商户绑定（pubkeyHash）。
     *
     * 含：证书链验证、signedAttrs 验签（0xA0→0x31，RFC 5652 §5.4）、
     * messageDigest 一致性、signingTime 有效期。
     *
     * @param array<string, mixed> $header
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     */
    private static function verifyAppleTokenSignature(AppleConfig $config, string $signature, string $signedContent, array $header): void
    {
        $p7 = base64_decode($signature, true);

        if (false === $p7 || '' === $p7) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: Apple 支付令牌 `signature` 非法');
        }

        // ContentInfo ::= SEQUENCE { contentType OID, content [0] EXPLICIT SignedData }
        $contentInfo = self::parseAppleAsn1($p7);

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

        // 证书 DER → PEM；配置兜底的 intermediate 一并参与 OID 定位（PKCS#7 只含 leaf 时兜底）
        $certs = [];

        if (null === $certificatesNode) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌 PKCS#7 缺少证书');
        }

        foreach ($certificatesNode['children'] as $certNode) {
            if (0x30 === $certNode['tag']) {
                $certs[] = self::pemWrap(self::reConstructDer($certNode), 'CERTIFICATE');
            }
        }

        if ([] === $certs) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌 PKCS#7 缺少证书');
        }

        $rootCa = $config->getAppleRootCa();

        if (empty($rootCa)) {
            throw new InvalidConfigException(Exception::CONFIG_APPLE_INVALID, '配置异常: Apple 支付令牌验签需要配置 -- [apple_root_ca]');
        }

        $rootPem = CertManager::getPublicCert($rootCa);

        $intermediateCa = $config->getAppleIntermediateCa();

        if (!empty($intermediateCa)) {
            $certs[] = CertManager::getPublicCert($intermediateCa);
        }

        $leafPem = self::verifyAppleChain($certs, $rootPem, self::APPLE_TOKEN_INTERMEDIATE_OID, self::APPLE_TOKEN_LEAF_OID);

        // 商户绑定：sha256(商户证书 SPKI DER) === header.publicKeyHash
        $merchantPem = CertManager::getPublicCert($config->getPaymentProcessingCert());
        $merchantPublicKey = openssl_pkey_get_public($merchantPem);

        if (false === $merchantPublicKey) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析 Apple 支付处理证书失败');
        }

        $merchantKeyDetails = openssl_pkey_get_details($merchantPublicKey);
        $merchantSpkiPem = (string) ($merchantKeyDetails['key'] ?? '');

        if ('' === $merchantSpkiPem) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析 Apple 支付处理证书失败');
        }

        // PEM 尾部可能带空行，按 BEGIN/END 行提取 base64 正文（不依赖行数偏移）
        $lines = preg_split('/\r?\n/', $merchantSpkiPem);

        if (false === $lines) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析 Apple 支付处理证书失败');
        }

        $base64 = '';

        foreach ($lines as $line) {
            if (str_starts_with($line, '-----BEGIN')) {
                continue;
            }

            if (str_starts_with($line, '-----END')) {
                break;
            }

            $base64 .= trim($line);
        }

        $merchantSpkiDer = base64_decode($base64, true);

        if (false === $merchantSpkiDer) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析 Apple 支付处理证书失败');
        }

        $tokenPublicKeyHash = base64_decode((string) ($header['publicKeyHash'] ?? ''), true);

        if (false === $tokenPublicKeyHash || !hash_equals(hash('sha256', $merchantSpkiDer, true), $tokenPublicKeyHash)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌商户证书哈希不匹配');
        }

        // RFC 5652 §5.4：signature 验证对象为 DER 编码的 SignedAttrs（0xA0 → 0x31）
        $der31 = self::reConstructDer($signedAttrsNode);

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

            if (self::encodeAsn1Oid(self::APPLE_OID_MESSAGE_DIGEST) === $oidDer) {
                if (0x04 === $innerValueNode['tag']) {
                    $messageDigest = $innerValueNode['value'];
                }
            } elseif (self::encodeAsn1Oid(self::APPLE_OID_SIGNING_TIME) === $oidDer) {
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

        if (false === $signingDateTime || abs(time() - $signingDateTime->getTimestamp()) > self::APPLE_SIGNING_TIME_WINDOW) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 支付令牌签名时间超出允许范围');
        }
    }

    /**
     * 按 OID 定位 leaf/intermediate 证书并逐级验证证书链，返回 leaf 证书 PEM。
     *
     * @param array<int, string> $certs 证书 PEM 列表（PKCS#7 内全部证书 + 配置兜底 intermediate）
     *
     * @throws InvalidSignException
     */
    private static function verifyAppleChain(array $certs, string $rootPem, string $intermediateOid, string $leafOid): string
    {
        $leafPem = null;
        $intermediatePem = null;

        foreach ($certs as $pem) {
            $info = openssl_x509_parse($pem);

            if (false === $info) {
                continue;
            }

            $extensions = $info['extensions'] ?? [];

            if (null === $leafPem && isset($extensions[$leafOid])) {
                $leafPem = $pem;
            }

            if (null === $intermediatePem && isset($extensions[$intermediateOid])) {
                $intermediatePem = $pem;
            }
        }

        if (null === $leafPem) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 证书链缺少 leaf 证书');
        }

        if (null === $intermediatePem) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 证书链缺少 intermediate 证书（可配置 -- [apple_intermediate_ca] 兜底）');
        }

        if (1 !== openssl_x509_verify($leafPem, $intermediatePem)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 证书链验证失败（leaf ← intermediate）');
        }

        if (1 !== openssl_x509_verify($intermediatePem, $rootPem)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 证书链验证失败（intermediate ← root）');
        }

        return $leafPem;
    }

    /**
     * ECDH + KDF 派生 AES-256-GCM 对称密钥（NIST SP 800-56A 计数器模式 KDF）。
     *
     * @throws InvalidConfigException
     */
    private static function deriveAppleSymmetricKey(string $ephemeralPublicKey, OpenSSLAsymmetricKey $merchantPrivateKey, string $merchantId): string
    {
        $ephemeralPublicKeyDer = base64_decode($ephemeralPublicKey, true);

        if (false === $ephemeralPublicKeyDer) {
            throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌 `header.ephemeralPublicKey` 非法');
        }

        $sharedSecret = openssl_pkey_derive(self::pemWrap($ephemeralPublicKeyDer, 'PUBLIC KEY'), $merchantPrivateKey);

        if (false === $sharedSecret) {
            throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌 ECDH 密钥协商失败');
        }

        // KDF（etsy applepay.c / PayU-EMEA Ecc.php 交叉核对）:
        // SHA256(0x00 0x00 0x00 0x01 || Z || 0x0D || "id-aes256-GCM" || "Apple" || SHA256(merchantId))
        return hash(
            'sha256',
            "\x00\x00\x00\x01".$sharedSecret.chr(0x0D).'id-aes256-GCMApple'.hash('sha256', trim($merchantId), true),
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
    private static function decryptAppleData(string $version, array $header, string $data, OpenSSLAsymmetricKey $merchantPrivateKey, string $merchantId): array
    {
        $raw = base64_decode($data, true);

        if (false === $raw || 16 > strlen($raw)) {
            throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌密文非法');
        }

        // iv 16 字节全零；tag 为密文末尾 16 字节
        $tag = substr($raw, -16);
        $cipherText = substr($raw, 0, -16);

        if ('EC_v1' === $version) {
            $symmetricKey = self::deriveAppleSymmetricKey((string) $header['ephemeralPublicKey'], $merchantPrivateKey, $merchantId);
            $cipher = 'aes-256-gcm';
        } else {
            if (PHP_VERSION_ID < 80500) {
                throw new InvalidConfigException(
                    Exception::DECRYPT_APPLE_FAILED,
                    '解密异常: Apple RSA_v1 令牌解密需要 PHP >= 8.5（OAEP-SHA256 参数限制），EC_v1 无此要求'
                );
            }

            $wrappedKey = base64_decode((string) $header['wrappedKey'], true);

            if (false === $wrappedKey) {
                throw new InvalidConfigException(Exception::DECRYPT_APPLE_FAILED, '解密异常: Apple 支付令牌 `header.wrappedKey` 非法');
            }

            // PHP 8.5 起 digest_algo 为第 5 位置参数（8.2–8.4 硬编码 OAEP SHA-1，故版本检查在入口）
            if (!openssl_private_decrypt($wrappedKey, $symmetricKey, $merchantPrivateKey, OPENSSL_PKCS1_OAEP_PADDING, 'sha256')) {
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
     * 验证单节点 JWS：x5c 全链验证（末位须与配置 root 一致）；无 x5c 时回退已信任 leaf 公钥。
     *
     * @return array{leafPem: string, payload: array<string, mixed>}
     *
     * @throws InvalidSignException
     */
    private static function verifyAppleJwsNode(string $signedPayload, string $rootDer, ?string $fallbackLeafPem): array
    {
        $parts = explode('.', $signedPayload);

        if (3 !== count($parts)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 格式非法');
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        if ('' === $headerB64 || '' === $payloadB64 || '' === $signatureB64) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: Apple JWS 签名为空');
        }

        $header = json_decode(self::base64UrlDecode($headerB64), true);

        if (!is_array($header) || 'ES256' !== ($header['alg'] ?? null)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 签名算法非法');
        }

        $leafPem = null;
        $x5c = $header['x5c'] ?? null;

        if (is_array($x5c)) {
            if (2 > count($x5c)) {
                throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 证书链不完整');
            }

            $lastCert = end($x5c);

            if (!is_string($lastCert)) {
                throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 证书链非法');
            }

            $lastDer = base64_decode($lastCert, true);

            // 末位证书 DER 须与配置 root DER 一致（不硬编码证书张数）
            if (false === $lastDer || !hash_equals($rootDer, $lastDer)) {
                throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 根证书不匹配');
            }

            $chainPems = [];

            foreach ($x5c as $cert) {
                if (!is_string($cert)) {
                    throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 证书链非法');
                }

                $certDer = base64_decode($cert, true);

                if (false === $certDer) {
                    throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 证书链非法');
                }

                $chainPems[] = self::pemWrap($certDer, 'CERTIFICATE');
            }

            for ($i = 0; $i < count($chainPems) - 1; ++$i) {
                if (1 !== openssl_x509_verify($chainPems[$i], $chainPems[$i + 1])) {
                    throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 证书链验证失败');
                }
            }

            $leafInfo = openssl_x509_parse($chainPems[0]);
            $intermediateInfo = openssl_x509_parse($chainPems[1]);

            if (false === $leafInfo || false === $intermediateInfo
                || !isset($leafInfo['extensions'][self::APPLE_JWS_LEAF_OID])
                || !isset($intermediateInfo['extensions'][self::APPLE_JWS_INTERMEDIATE_OID])
            ) {
                throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 证书 OID 不匹配');
            }

            $leafPem = $chainPems[0];
        } else {
            // 二级 JWS（signedTransactionInfo/signedRenewalInfo）通常无 x5c：使用已信任 leaf 公钥
            if (null === $fallbackLeafPem) {
                throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 缺少证书链');
            }

            $leafPem = $fallbackLeafPem;
        }

        $leafPublicKey = openssl_pkey_get_public($leafPem);
        // PHP openssl_verify() 对 EC 密钥仅接受 DER 签名；JWS 签名为 raw（r || s）→ 先转 DER
        $signatureDer = self::rawToDerAppleSignature(self::base64UrlDecode($signatureB64));

        if (false === $leafPublicKey || 1 !== openssl_verify($headerB64.'.'.$payloadB64, $signatureDer, $leafPublicKey, OPENSSL_ALGO_SHA256)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 签名验证失败');
        }

        $payload = json_decode(self::base64UrlDecode($payloadB64), true);

        if (!is_array($payload)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS payload 非法');
        }

        return ['leafPem' => $leafPem, 'payload' => $payload];
    }

    /**
     * JWS 的 raw ECDSA 签名（r || s，各 32 字节）→ DER（与 derToRawApple() 对称）。
     *
     * @throws InvalidSignException
     */
    private static function rawToDerAppleSignature(string $raw): string
    {
        if (64 !== strlen($raw)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 签名长度非法');
        }

        $r = ltrim(substr($raw, 0, 32), "\x00");
        $s = ltrim(substr($raw, 32, 32), "\x00");

        if ('' === $r) {
            $r = "\x00";
        }

        if ('' === $s) {
            $s = "\x00";
        }

        // 最高位为 1 时补 0x00（符号位填充），见 RFC 3279 / SEC1
        if (0 !== (ord($r[0]) & 0x80)) {
            $r = "\x00".$r;
        }

        if (0 !== (ord($s[0]) & 0x80)) {
            $s = "\x00".$s;
        }

        $sequence = "\x02".chr(strlen($r)).$r."\x02".chr(strlen($s)).$s;

        return "\x30".chr(strlen($sequence)).$sequence;
    }

    /**
     * ECDSA DER 签名转 raw 格式（r || s，各 32 字节）。
     *
     * @throws InvalidSignException
     */
    private static function derToRawApple(string $derSignature): string
    {
        $sequence = self::parseAppleAsn1($derSignature);

        if (0x30 !== $sequence['tag'] || 2 !== count($sequence['children'])
            || 0x02 !== $sequence['children'][0]['tag']
            || 0x02 !== $sequence['children'][1]['tag']
        ) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: ECDSA DER 签名结构非法');
        }

        $r = self::stripAsn1IntegerPrefix($sequence['children'][0]['value']);
        $s = self::stripAsn1IntegerPrefix($sequence['children'][1]['value']);

        return str_pad($r, 32, "\x00", STR_PAD_LEFT).str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    /**
     * 剥 INTEGER 前导 0x00（符号位填充），仅当下一字节高位为 1 时剥除。
     */
    private static function stripAsn1IntegerPrefix(string $value): string
    {
        if (strlen($value) > 1 && "\x00" === $value[0] && 0 !== (ord($value[1]) & 0x80)) {
            return substr($value, 1);
        }

        return $value;
    }
}
