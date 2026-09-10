<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Provider\Bestpay;
use Yansongda\Supports\Collection;

trait BestpayTrait
{
    use ProviderConfigTrait;

    public static function getBestpayUrl(BestpayConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload) ?? '';

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        if ('' === $url) {
            $url = Bestpay::PATH_SDK_REQUEST;
        }

        $base = Bestpay::URL[$config->getMode()] ?? Bestpay::URL[0];

        return rtrim($base, '/').$url;
    }

    /**
     * 按 key 升序拼接待签串：k=v&k=v（排除 sign）.
     *
     * @param array<string, mixed> $data
     */
    public static function getBestpaySignContent(array $data): string
    {
        unset($data['sign']);
        ksort($data);

        $pairs = [];

        foreach ($data as $key => $value) {
            if (null === $value || '' === $value) {
                continue;
            }

            $pairs[] = $key.'='.$value;
        }

        return implode('&', $pairs);
    }

    /**
     * SHA256withRSA 加签，返回 Base64.
     *
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function signBestpayContent(BestpayConfig $config, string $content): string
    {
        $certs = CertManager::unipayGetPkcs12Certs(
            $config->getMchSecretCertPath(),
            $config->getMchSecretCertPassword()
        );

        $privateKey = $certs['pkey'] ?? null;

        if (empty($privateKey)) {
            throw new InvalidConfigException(Exception::CONFIG_BESTPAY_INVALID, '配置异常: 翼支付商户私钥解析失败');
        }

        if (!openssl_sign($content, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 翼支付加签失败', func_get_args());
        }

        return base64_encode($signature);
    }

    /**
     * 响应/回调验签（平台公钥）.
     *
     * Demo 使用 SHA1withRSA；联调若失败可再试 SHA256withRSA.
     *
     * @param array<string, mixed> $data 含 sign 的完整报文
     *
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verifyBestpaySign(BestpayConfig $config, array $data): void
    {
        $sign = (string) ($data['sign'] ?? '');

        if ('' === $sign) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 翼支付签名为空', func_get_args());
        }

        $content = self::getBestpaySignContent($data);
        $publicCertPath = $config->getBestpayPublicCertPath();

        if (empty($publicCertPath)) {
            throw new InvalidConfigException(Exception::CONFIG_BESTPAY_INVALID, '配置异常: 缺少翼支付配置 -- [bestpay_public_cert_path]');
        }

        $publicKey = openssl_pkey_get_public(CertManager::getPublicCert($publicCertPath));

        if (false === $publicKey) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析翼支付平台公钥失败');
        }

        $decoded = base64_decode($sign, true);

        if (false === $decoded) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 翼支付签名 Base64 解码失败', func_get_args());
        }

        $okSha1 = 1 === openssl_verify($content, $decoded, $publicKey, OPENSSL_ALGO_SHA1);
        $okSha256 = 1 === openssl_verify($content, $decoded, $publicKey, OPENSSL_ALGO_SHA256);

        if (!$okSha1 && !$okSha256) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证翼支付签名失败', func_get_args());
        }
    }

    /**
     * MAPI SDK 响应验签：嵌套对象先转有序 JSON，再按 k=v& 拼串（对齐官方 Java SDK）.
     *
     * @param array<string, mixed> $data 含 sign 的完整响应报文
     *
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verifyBestpayResponseSign(BestpayConfig $config, array $data): void
    {
        $sign = (string) ($data['sign'] ?? '');

        if ('' === $sign) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 翼支付响应签名为空', func_get_args());
        }

        $content = self::getBestpayResponseSignContent($data);
        $publicCertPath = $config->getBestpayPublicCertPath();

        if (empty($publicCertPath)) {
            throw new InvalidConfigException(Exception::CONFIG_BESTPAY_INVALID, '配置异常: 缺少翼支付配置 -- [bestpay_public_cert_path]');
        }

        $publicKey = openssl_pkey_get_public(CertManager::getPublicCert($publicCertPath));

        if (false === $publicKey) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析翼支付平台公钥失败');
        }

        $decoded = base64_decode($sign, true);

        if (false === $decoded) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 翼支付响应签名 Base64 解码失败', func_get_args());
        }

        $okSha1 = 1 === openssl_verify($content, $decoded, $publicKey, OPENSSL_ALGO_SHA1);
        $okSha256 = 1 === openssl_verify($content, $decoded, $publicKey, OPENSSL_ALGO_SHA256);

        if (!$okSha1 && !$okSha256) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证翼支付响应签名失败', func_get_args());
        }
    }

    /**
     * 响应待签串：除 sign 外全量字段，嵌套数组转有序 JSON，bool/null 保留语义.
     *
     * @param array<string, mixed> $data
     */
    public static function getBestpayResponseSignContent(array $data): string
    {
        unset($data['sign']);
        ksort($data);

        $pairs = [];

        foreach ($data as $key => $value) {
            $pairs[] = $key.'='.self::stringifySignValue($value);
        }

        return implode('&', $pairs);
    }

    private static function stringifySignValue(mixed $value): string
    {
        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (null === $value) {
            return 'null';
        }

        if (is_array($value)) {
            return (string) json_encode(self::ksortRecursive($value), JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    /**
     * @param array<mixed> $value
     *
     * @return array<mixed>
     */
    private static function ksortRecursive(array $value): array
    {
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $value[$k] = self::ksortRecursive($v);
            }
        }

        if (array_is_list($value)) {
            return $value;
        }

        ksort($value);

        return $value;
    }
}
