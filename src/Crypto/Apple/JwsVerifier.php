<?php

declare(strict_types=1);

namespace Yansongda\Pay\Crypto\Apple;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;

/**
 * App Store Server Notifications V2 JWS 验签。
 *
 * x5c 全链验签（锚 Apple Root CA - G3）→ 归属校验（bundleId/environment）
 * → 内嵌交易/续订 JWS 二级解码。每个 JWS 均独立全链验证。
 *
 * 通用密码学工具见 Cryptor，支付令牌域见 TokenVerifier。
 */
final class JwsVerifier
{
    /* Apple 证书扩展 OID：App Store Server Notifications V2（JWS） */
    private const JWS_INTERMEDIATE_OID = '1.2.840.113635.100.6.2.1';
    private const JWS_LEAF_OID = '1.2.840.113635.100.6.11.1';

    private function __construct() {}

    /**
     * 验证 App Store Server Notifications V2 JWS 签名。
     *
     * @return array<string, mixed>
     *
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verify(string $signedPayload, AppleConfig $config): array
    {
        $rootDer = Cryptor::pemToDer(
            CertManager::getPublicCert($config->getAppleRootCa()),
            '配置异常: 解析 Apple 根证书失败'
        );

        $payload = self::verifyNode($signedPayload, $rootDer);

        // 归属校验：防「真实 Apple 签名但错误归属」的伪造通知（跨 App/跨环境）
        self::assertOwnership($payload, $config);

        if (isset($payload['data']) && is_array($payload['data'])) {
            foreach (['signedTransactionInfo', 'signedRenewalInfo'] as $key) {
                if (isset($payload['data'][$key])) {
                    $payload['data'][$key] = self::verifyNode(
                        (string) $payload['data'][$key],
                        $rootDer
                    );
                }
            }
        }

        return $payload;
    }

    /**
     * 校验 JWS 解码数据的归属（bundleId/environment 与配置匹配），
     * 对齐官方库（app-store-server-library）防跨 App/跨环境伪造。
     *
     * 通知（ResponseBodyV2）形态取 data/summary/externalPurchaseToken/appData 首个存在分支；
     * 交易/续订（signedTransactionInfo/signedRenewalInfo）形态字段在顶层。
     * 未配置 bundle_id 的租户（如仅验签解密租户）跳过 bundleId 校验，environment 始终校验。
     *
     * @param array<string, mixed> $payload
     *
     * @throws InvalidSignException
     */
    private static function assertOwnership(array $payload, AppleConfig $config): void
    {
        $branch = null;
        $branchKey = '';

        foreach (['data', 'summary', 'externalPurchaseToken', 'appData'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                $branch = $payload[$key];
                $branchKey = $key;

                break;
            }
        }

        if (null === $branch && isset($payload['bundleId'])) {
            $branch = $payload;
        }

        if (null === $branch) {
            // 无法识别的数据形态（如二级 JWS），签名验证已通过，不做归属校验
            return;
        }

        $configuredBundleId = $config->getBundleId();

        if (!empty($configuredBundleId)
            && (string) ($branch['bundleId'] ?? '') !== $configuredBundleId
        ) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 通知归属校验失败 -- [bundleId] 与配置不匹配');
        }

        $expected = Pay::MODE_SANDBOX === $config->getMode() ? 'Sandbox' : 'Production';

        // externalPurchaseToken 分支无 environment 字段，按官方库从 externalPurchaseId 前缀推断
        $environment = 'externalPurchaseToken' === $branchKey
            ? (str_starts_with((string) ($branch['externalPurchaseId'] ?? ''), 'SANDBOX') ? 'Sandbox' : 'Production')
            : (string) ($branch['environment'] ?? '');

        if ('' === $environment || $environment !== $expected) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 通知归属校验失败 -- [environment] 与配置模式不匹配');
        }
    }

    /**
     * 验证单节点 JWS：x5c 全链验证（末位须与配置 root 一致），每个 JWS 均独立全链验证。
     *
     * @return array<string, mixed>
     *
     * @throws InvalidSignException
     */
    private static function verifyNode(string $signedPayload, string $rootDer): array
    {
        $parts = explode('.', $signedPayload);

        if (3 !== count($parts)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 格式非法');
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        if ('' === $headerB64 || '' === $payloadB64 || '' === $signatureB64) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: Apple JWS 签名为空');
        }

        $header = json_decode(Cryptor::base64UrlDecode($headerB64), true);

        if (!is_array($header) || 'ES256' !== ($header['alg'] ?? null)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 签名算法非法');
        }

        $x5c = $header['x5c'] ?? null;

        // 对齐官方库（app-store-server-library）：每个 JWS 均须自带 x5c 证书链，独立全链验证
        if (!is_array($x5c)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 缺少证书链');
        }

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

            $chainPems[] = Cryptor::pemWrap($certDer, 'CERTIFICATE');
        }

        $chainInfos = [];

        foreach ($chainPems as $chainPem) {
            $chainInfo = openssl_x509_parse($chainPem);

            if (false === $chainInfo) {
                throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 证书链非法');
            }

            Cryptor::assertCertNotExpired($chainInfo, 'JWS x5c');
            $chainInfos[] = $chainInfo;
        }

        for ($i = 0; $i < count($chainPems) - 1; ++$i) {
            if (1 !== openssl_x509_verify($chainPems[$i], $chainPems[$i + 1])) {
                throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 证书链验证失败');
            }
        }

        if (!isset($chainInfos[0]['extensions'][self::JWS_LEAF_OID])
            || !isset($chainInfos[1]['extensions'][self::JWS_INTERMEDIATE_OID])
        ) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 证书 OID 不匹配');
        }

        $leafPublicKey = openssl_pkey_get_public($chainPems[0]);
        // PHP openssl_verify() 对 EC 密钥仅接受 DER 签名；JWS 签名为 raw（r || s）→ 先转 DER
        $signatureDer = Cryptor::rawToDerSignature(Cryptor::base64UrlDecode($signatureB64));

        if (false === $leafPublicKey || 1 !== openssl_verify($headerB64.'.'.$payloadB64, $signatureDer, $leafPublicKey, OPENSSL_ALGO_SHA256)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 签名验证失败');
        }

        $payload = json_decode(Cryptor::base64UrlDecode($payloadB64), true);

        if (!is_array($payload)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS payload 非法');
        }

        return $payload;
    }
}
