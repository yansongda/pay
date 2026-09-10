<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
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

        $base = Bestpay::URL[$config->getMode()] ?? Bestpay::URL[Pay::MODE_NORMAL];

        return rtrim($base, '/').$url;
    }

    /**
     * 按 key 升序拼接待签串：k=v&k=v（排除 sign）.
     *
     * 对齐官方 Java SDK（mapi-sdk `AssembleUtil`）语义：
     * - TreeMap 升序（ksort）
     * - 不跳过任何字段：`null` 拼为 `k=null`、空串拼为 `k=`（`StringBuilder.append` 语义）
     * - bool 拼为 `k=true` / `k=false`（`String.valueOf` 语义）
     * - 嵌套 Map/List 转为 key 升序后的 JSON 字符串（`translateMapData` 的
     *   `MapSortField` + `WriteMapNullValue` 语义）
     *
     * @see https://github.com/Belos10/DiningOrder/blob/master/src/main/java/com/example/utils/payUtil/AssembleUtil.java AssembleUtil.AssembleSignatureData/translateMapData
     *
     * @param array<string, mixed> $data
     */
    public static function getBestpaySignContent(array $data): string
    {
        unset($data['sign']);
        ksort($data);

        $pairs = [];

        foreach ($data as $key => $value) {
            $pairs[] = $key.'='.self::stringifySignValue($value);
        }

        return implode('&', $pairs);
    }

    /**
     * SHA256withRSA 加签（商户 PKCS12 私钥），返回 Base64.
     *
     * @see https://github.com/Belos10/DiningOrder/blob/master/src/main/java/com/example/utils/payUtil/SignEncryptUtil.java SignEncryptUtil.sign
     *
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function signBestpayContent(BestpayConfig $config, string $content): string
    {
        $certs = CertManager::getPkcs12Certs(
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
     * 官方 Demo 使用 SHA1withRSA 验签，请求加签为 SHA256withRSA；
     * 两者皆尝试，以实际联调为准.
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

        $decoded = base64_decode($sign, true);

        if (false === $decoded) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 翼支付签名 Base64 解码失败', func_get_args());
        }

        $publicCertPath = $config->getBestpayPublicCertPath();

        if (empty($publicCertPath)) {
            throw new InvalidConfigException(Exception::CONFIG_BESTPAY_INVALID, '配置异常: 缺少翼支付配置 -- [bestpay_public_cert_path]');
        }

        $publicKey = openssl_pkey_get_public(CertManager::getPublicCert($publicCertPath));

        if (false === $publicKey) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析翼支付平台公钥失败');
        }

        $content = self::getBestpaySignContent($data);

        $okSha1 = 1 === openssl_verify($content, $decoded, $publicKey, OPENSSL_ALGO_SHA1);
        $okSha256 = $okSha1 || 1 === openssl_verify($content, $decoded, $publicKey, OPENSSL_ALGO_SHA256);

        if (!$okSha256) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证翼支付签名失败', func_get_args());
        }
    }

    /**
     * 签名值序列化：bool/null 保留字面语义，嵌套数组转 key 升序 JSON.
     */
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
