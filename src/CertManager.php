<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Supports\Str;

class CertManager
{
    private static array $cache = [];
    private static array $certs = [];

    public static function getPublicCert(string $key): string
    {
        $cacheKey = 'public_'.md5($key);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $cert = is_file($key) ? file_get_contents($key) : $key;

        self::$cache[$cacheKey] = $cert;

        return $cert;
    }

    public static function getPrivateCert(string $key): string
    {
        $cacheKey = 'private_'.md5($key);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        if (is_file($key)) {
            $cert = file_get_contents($key);
        } elseif (Str::startsWith($key, '-----BEGIN PRIVATE KEY-----')) {
            $cert = $key;
        } else {
            $cert = "-----BEGIN RSA PRIVATE KEY-----\n"
                .wordwrap($key, 64, "\n", true)
                ."\n-----END RSA PRIVATE KEY-----";
        }

        self::$cache[$cacheKey] = $cert;

        return $cert;
    }

    public static function clearCache(): void
    {
        self::$cache = [];
        self::$certs = [];
    }

    /**
     * 按 provider / tenant / serialNo 保存证书内容。
     */
    public static function setBySerial(string $provider, string $tenant, string $serialNo, string $cert): void
    {
        self::$certs[$provider][$tenant][$serialNo] = $cert;
    }

    /**
     * 按 provider / tenant 批量保存证书内容。
     *
     * @param array<string, string> $certs
     */
    public static function setAllBySerial(string $provider, string $tenant, array $certs): void
    {
        self::$certs[$provider][$tenant] = $certs;
    }

    /**
     * 获取指定 provider / tenant / serialNo 的证书内容。
     */
    public static function getBySerial(string $provider, string $tenant, string $serialNo): ?string
    {
        return self::$certs[$provider][$tenant][$serialNo] ?? null;
    }

    /**
     * 获取指定 provider / tenant 下的所有证书内容。
     *
     * @return array<string, string>
     */
    public static function getAllBySerial(string $provider, string $tenant): array
    {
        return self::$certs[$provider][$tenant] ?? [];
    }

    /**
     * 判断指定 provider / tenant / serialNo 的证书是否存在。
     */
    public static function hasBySerial(string $provider, string $tenant, string $serialNo): bool
    {
        return isset(self::$certs[$provider][$tenant][$serialNo]);
    }

    /**
     * 清除指定 provider / tenant 下的证书缓存。
     */
    public static function clearBySerial(string $provider, string $tenant): void
    {
        unset(self::$certs[$provider][$tenant]);
    }

    /**
     * 清除全部按 serialNo 存储的证书缓存。
     */
    public static function clearAllBySerial(): void
    {
        self::$certs = [];
    }
}
