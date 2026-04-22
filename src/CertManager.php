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

    private static function getCachedContent(string $type, string $key, callable $loader): string
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
}
