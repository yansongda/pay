<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Str;

class CertManager
{
    /**
     * 文件内容/证书解析结果缓存.
     *
     * 键格式: {type}_{sha1($key)}，如 "public_info_a1b2c3..."
     * 用于缓存: 证书文件内容、解析后的证书信息、支付宝 SN、银联证书 ID 等
     * 避免重复读取文件和重复解析证书.
     */
    private static array $cache = [];

    /**
     * 微信平台证书缓存.
     *
     * 结构: [$tenant][$serialNo] = $certContent
     * 用于: 微信回调验签时，根据证书序列号快速查找对应公钥证书
     * 支持多租户隔离，每个租户的证书缓存独立.
     */
    private static array $wechatCerts = [];

    public static function getPublicCert(string $key): string
    {
        return self::getCachedContent(
            'public',
            $key,
            fn (string $k): string => is_file($k) ? file_get_contents($k) : $k
        );
    }

    public static function getPrivateCert(string $key): string
    {
        return self::getCachedContent('private', $key, function (string $k): string {
            if (is_file($k)) {
                return file_get_contents($k);
            }

            if (Str::startsWith($k, '-----BEGIN PRIVATE KEY-----')) {
                return $k;
            }

            return "-----BEGIN RSA PRIVATE KEY-----\n"
                .wordwrap($k, 64, "\n", true)
                ."\n-----END RSA PRIVATE KEY-----";
        });
    }

    /**
     * 获取并缓存公钥证书解析结果。
     *
     * @throws InvalidConfigException 证书解析失败
     */
    public static function getPublicCertInfo(string $key): array
    {
        return self::getCachedContent('public_info', $key, function (string $k): array {
            $info = openssl_x509_parse(self::getPublicCert($k));

            if (false === $info) {
                throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析证书失败');
            }

            return $info;
        });
    }

    /**
     * 获取支付宝应用公钥证书序列号.
     *
     * @throws InvalidConfigException 证书解析失败
     */
    public static function alipayGetAppCertSn(string $key): string
    {
        return self::getCachedContent('alipay_app_cert_sn', $key, function (string $k): string {
            $ssl = self::getPublicCertInfo($k);

            return self::alipayGetCertSn($ssl['issuer'] ?? [], $ssl['serialNumber'] ?? '');
        });
    }

    /**
     * 获取支付宝根证书序列号.
     *
     * @throws InvalidConfigException 证书解析失败
     */
    public static function alipayGetRootCertSn(string $key): string
    {
        return self::getCachedContent('alipay_root_cert_sn', $key, function (string $k): string {
            $sn = '';
            $exploded = explode('-----END CERTIFICATE-----', self::getPublicCert($k));

            foreach ($exploded as $cert) {
                if (empty(trim($cert))) {
                    continue;
                }

                $ssl = openssl_x509_parse($cert.'-----END CERTIFICATE-----');

                if (false === $ssl) {
                    throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析 `alipay_root_cert` 失败');
                }

                $detail = self::alipayFormatCert($ssl);

                if ('sha1WithRSAEncryption' == $detail['signatureTypeLN'] || 'sha256WithRSAEncryption' == $detail['signatureTypeLN']) {
                    $sn .= self::alipayGetCertSn($detail['issuer'], $detail['serialNumber']).'_';
                }
            }

            return substr($sn, 0, -1);
        });
    }

    /**
     * 获取银联 PKCS12 证书内容.
     *
     * @throws InvalidConfigException 证书读取失败
     */
    public static function unipayGetPkcs12Certs(string $path, string $password): array
    {
        return self::getCachedContent('unipay_pkcs12', $path.$password, function () use ($path, $password): array {
            $content = is_file($path) ? file_get_contents($path) : $path;
            $certs = [];

            if (false === openssl_pkcs12_read($content, $certs, $password)) {
                throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 读取证书失败，确认参数是否正确');
            }

            return $certs;
        });
    }

    /**
     * 获取银联证书序列号.
     *
     * @throws InvalidConfigException 证书解析失败
     */
    public static function unipayGetCertId(string $key, string $password): string
    {
        return self::getCachedContent('unipay_cert_id', $key.$password, function () use ($key, $password): string {
            $certs = self::unipayGetPkcs12Certs($key, $password);
            $ssl = openssl_x509_parse($certs['cert'] ?? '');

            if (false === $ssl) {
                throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, '配置异常: 解析证书失败，请检查参数是否正确');
            }

            return $ssl['serialNumber'] ?? '';
        });
    }

    public static function clearCache(): void
    {
        self::$cache = [];
        self::$wechatCerts = [];
    }

    /**
     * 设置微信平台证书缓存.
     */
    public static function wechatSetCertBySerial(string $tenant, string $serialNo, string $cert): void
    {
        self::$wechatCerts[$tenant][$serialNo] = $cert;
    }

    /**
     * 获取微信平台证书缓存.
     */
    public static function wechatGetCertBySerial(string $tenant, string $serialNo): ?string
    {
        return self::$wechatCerts[$tenant][$serialNo] ?? null;
    }

    /**
     * 获取所有微信平台证书缓存.
     */
    public static function wechatGetAllCertsBySerial(string $tenant): array
    {
        return self::$wechatCerts[$tenant] ?? [];
    }

    private static function getCachedContent(string $type, string $key, callable $loader): mixed
    {
        $cacheKey = $type.'_'.sha1($key);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        self::$cache[$cacheKey] = $loader($key);

        return self::$cache[$cacheKey];
    }

    private static function alipayGetCertSn(array $issuer, string $serialNumber): string
    {
        return md5(self::alipayArrayToString(array_reverse($issuer)).$serialNumber);
    }

    private static function alipayArrayToString(array $array): string
    {
        $string = [];

        foreach ($array as $key => $value) {
            $string[] = $key.'='.$value;
        }

        return implode(',', $string);
    }

    private static function alipayFormatCert(array $ssl): array
    {
        if (str_starts_with($ssl['serialNumber'] ?? '', '0x')) {
            $ssl['serialNumber'] = self::alipayHexToDec($ssl['serialNumberHex'] ?? '');
        }

        return $ssl;
    }

    private static function alipayHexToDec(string $hex): string
    {
        $dec = '0';
        $len = strlen($hex);

        for ($i = 1; $i <= $len; ++$i) {
            $dec = bcadd(
                $dec,
                bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i), 0), 0),
                0
            );
        }

        return $dec;
    }
}
