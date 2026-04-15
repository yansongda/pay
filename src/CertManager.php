<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Supports\Str;

class CertManager
{
    private static array $cache = [];

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
    }
}
