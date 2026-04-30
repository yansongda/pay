<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Str;

class CertManager
{
    private static array $cache = [];
    private static array $certs = [];

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
     */
    public static function getPublicCertInfo(string $key): array
    {
        return self::getCachedContent('public_info', $key, function (string $k): array {
            $info = openssl_x509_parse(self::getPublicCert($k));

            if (false === $info) {
                throw new InvalidConfigException(Exception::UNKNOWN_ERROR, '配置异常: 解析证书失败');
            }

            return $info;
        });
    }

    public static function getAlipayAppCertSn(string $key): string
    {
        return self::getCachedContent('alipay_app_cert_sn', $key, function (string $k): string {
            $ssl = self::getPublicCertInfo($k);

            return self::getAlipayCertSn($ssl['issuer'] ?? [], $ssl['serialNumber'] ?? '');
        });
    }

    public static function getAlipayRootCertSn(string $key): string
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
                    throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 解析 `alipay_root_cert` 失败');
                }

                $detail = self::formatAlipayCert($ssl);

                if ('sha1WithRSAEncryption' == $detail['signatureTypeLN'] || 'sha256WithRSAEncryption' == $detail['signatureTypeLN']) {
                    $sn .= self::getAlipayCertSn($detail['issuer'], $detail['serialNumber']).'_';
                }
            }

            return substr($sn, 0, -1);
        });
    }

    public static function getPkcs12Certs(string $path, string $password): array
    {
        return self::getCachedContent('pkcs12', $path.$password, function (string $k) use ($path, $password): array {
            $content = is_file($path) ? file_get_contents($path) : $path;
            $certs = [];

            if (false === openssl_pkcs12_read($content, $certs, $password)) {
                throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 读取证书失败，确认参数是否正确');
            }

            return $certs;
        });
    }

    public static function getUnipayCertId(string $key, string $password): string
    {
        return self::getCachedContent('unipay_cert_id', $key.$password, function (string $k) use ($key, $password): string {
            $certs = self::getPkcs12Certs($key, $password);
            $ssl = openssl_x509_parse($certs['cert'] ?? '');

            if (false === $ssl) {
                throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 解析证书失败，请检查参数是否正确');
            }

            return $ssl['serialNumber'] ?? '';
        });
    }

    public static function clearCache(): void
    {
        self::$cache = [];
        self::$certs = [];
    }

    public static function setBySerial(string $provider, string $tenant, string $serialNo, string $cert): void
    {
        self::$certs[$provider][$tenant][$serialNo] = $cert;
    }

    public static function setAllBySerial(string $provider, string $tenant, array $certs): void
    {
        self::$certs[$provider][$tenant] = $certs;
    }

    public static function getBySerial(string $provider, string $tenant, string $serialNo): ?string
    {
        return self::$certs[$provider][$tenant][$serialNo] ?? null;
    }

    public static function getAllBySerial(string $provider, string $tenant): array
    {
        return self::$certs[$provider][$tenant] ?? [];
    }

    public static function hasBySerial(string $provider, string $tenant, string $serialNo): bool
    {
        return isset(self::$certs[$provider][$tenant][$serialNo]);
    }

    public static function clearBySerial(string $provider, string $tenant): void
    {
        unset(self::$certs[$provider][$tenant]);
    }

    public static function clearAllBySerial(): void
    {
        self::$certs = [];
    }

    private static function getCachedContent(string $type, string $key, callable $loader): mixed
    {
        $cacheKey = self::buildCacheKey($type, $key);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        self::$cache[$cacheKey] = $loader($key);

        return self::$cache[$cacheKey];
    }

    private static function buildCacheKey(string $type, string $key): string
    {
        return $type.'_'.sha1($key);
    }

    private static function getAlipayCertSn(array $issuer, string $serialNumber): string
    {
        return md5(self::alipayArray2String(array_reverse($issuer)).$serialNumber);
    }

    private static function alipayArray2String(array $array): string
    {
        $string = [];

        foreach ($array as $key => $value) {
            $string[] = $key.'='.$value;
        }

        return implode(',', $string);
    }

    private static function formatAlipayCert(array $ssl): array
    {
        if (str_starts_with($ssl['serialNumber'] ?? '', '0x')) {
            $ssl['serialNumber'] = self::alipayHex2Dec($ssl['serialNumberHex'] ?? '');
        }

        return $ssl;
    }

    private static function alipayHex2Dec(string $hex): string
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
