<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Crypto\Apple\Cryptor;
use Yansongda\Pay\Crypto\Apple\JwsVerifier;
use Yansongda\Pay\Crypto\Apple\TokenVerifier;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Apple;
use Yansongda\Supports\Collection;

/**
 * Apple Pay 对外唯一 Trait：URL 组装、App Store Server API JWT，以及两个本地密码学能力的入口。
 *
 * 实现按职责拆分至 src/Crypto/Apple（非 Trait，均为 final class + public static）：
 * - Cryptor：ASN.1 / PEM / base64url / hex / ECDSA raw↔DER / 证书链验证等通用工具
 * - TokenVerifier：支付令牌（payToken）PKCS#7 验签 + ECDH-KDF + AES-GCM/RSA-OAEP 解密
 * - JwsVerifier：App Store Server Notifications V2 JWS（x5c 证书链）验签 + 归属校验
 */
trait AppleTrait
{
    use ProviderConfigTrait;

    /** App Store Server API JWT 有效期（秒） */
    private const APPLE_JWT_TTL = 300;

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
            'exp' => $now + self::APPLE_JWT_TTL,
            'aud' => 'appstoreconnect-v1',
            'bid' => $bundleId,
        ];

        $headerB64 = Cryptor::base64UrlEncode((string) json_encode($header));
        $payloadB64 = Cryptor::base64UrlEncode((string) json_encode($payload));

        if (!openssl_sign($headerB64.'.'.$payloadB64, $derSignature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new InvalidConfigException(Exception::CONFIG_APPLE_INVALID, '配置异常: Apple App Store Server API JWT 签名失败 -- [api_private_key]');
        }

        return $headerB64.'.'.$payloadB64.'.'.Cryptor::base64UrlEncode(Cryptor::derToRawSignature($derSignature));
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

        if (!is_array($token)) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: Apple 支付令牌结构非法');
        }

        return new Collection(TokenVerifier::verify($config, $token));
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

        return JwsVerifier::verify($signedPayload, $config);
    }
}
