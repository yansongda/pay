<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Apple;

use RuntimeException;

/**
 * App Store Server Notifications V2 JWS 测试 Factory。
 *
 * 读取 jws-leaf / jws-intermediate / token-root fixture 证书构造顶级 JWS；
 * 反路径用例（ShortChain / Tampered）由测试内基于 fixture 自行构造 header 与签名。
 */
final class AppleJwsFactory
{
    private function __construct()
    {
    }

    /**
     * 构造顶级 JWS：header 含 x5c = [leaf, intermediate, root]，ES256 签名。
     *
     * @param array<string, mixed> $payload
     */
    public static function makeJws(array $payload): string
    {
        $header = [
            'alg' => 'ES256',
            'x5c' => [
                base64_encode(self::certDer('jws-leaf.crt')),
                base64_encode(self::certDer('jws-intermediate.crt')),
                base64_encode(self::certDer('token-root.crt')),
            ],
        ];

        return self::signJws($header, $payload);
    }

    /**
     * @param array<string, mixed> $header
     * @param array<string, mixed> $payload
     */
    private static function signJws(array $header, array $payload): string
    {
        $headerB64 = self::base64UrlEncode((string) json_encode($header));
        $payloadB64 = self::base64UrlEncode((string) json_encode($payload));

        $privateKey = openssl_pkey_get_private((string) file_get_contents(self::certPath('jws-leaf.key')));

        if (false === $privateKey) {
            throw new RuntimeException('读取 jws-leaf 私钥失败');
        }

        $derSignature = '';

        if (!openssl_sign($headerB64.'.'.$payloadB64, $derSignature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('ES256 签名失败');
        }

        return $headerB64.'.'.$payloadB64.'.'.self::base64UrlEncode(self::derToRaw($derSignature));
    }

    /**
     * ECDSA DER 签名 → raw（r || s，各 32 字节），测试侧独立实现。
     */
    private static function derToRaw(string $der): string
    {
        $offset = 0;

        if ('0' !== self::readTag($der, $offset, "\x30")) {
            throw new RuntimeException('ECDSA DER 签名结构非法');
        }

        $sequenceLength = self::readLength($der, $offset);
        $sequenceEnd = $offset + $sequenceLength;
        $r = self::readInteger($der, $offset);
        $s = self::readInteger($der, $offset);

        if ($sequenceEnd !== $offset) {
            throw new RuntimeException('ECDSA DER 签名结构非法');
        }

        return str_pad($r, 32, "\x00", STR_PAD_LEFT).str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    private static function readInteger(string $der, int &$offset): string
    {
        if ("\x02" !== self::readTag($der, $offset, "\x02")) {
            throw new RuntimeException('ECDSA DER INTEGER 非法');
        }

        $length = self::readLength($der, $offset);
        $value = substr($der, $offset, $length);
        $offset += $length;

        // 剥前导 0x00（符号位填充，仅当下一字节高位为 1）
        if (1 < strlen($value) && "\x00" === $value[0] && 0 !== (ord($value[1]) & 0x80)) {
            $value = substr($value, 1);
        }

        return $value;
    }

    private static function readTag(string $der, int &$offset, string $expected): string
    {
        $tag = $der[$offset++] ?? '';

        if ($tag !== $expected) {
            throw new RuntimeException('ECDSA DER tag 非法');
        }

        return $tag;
    }

    private static function readLength(string $der, int &$offset): int
    {
        $length = ord($der[$offset++] ?? '');

        if (0 !== ($length & 0x80)) {
            $count = $length & 0x7F;
            $length = 0;

            for ($i = 0; $i < $count; ++$i) {
                $length = ($length << 8) | ord($der[$offset++] ?? '');
            }
        }

        return $length;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function certDer(string $name): string
    {
        $pem = (string) file_get_contents(self::certPath($name));
        $lines = preg_split('/\r?\n/', $pem);

        if (false === $lines) {
            throw new RuntimeException('解析证书 PEM 失败');
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

        if (false === $der) {
            throw new RuntimeException('证书 DER 解码失败: '.$name);
        }

        return $der;
    }

    private static function certPath(string $name): string
    {
        return dirname(__DIR__, 2).'/Cert/apple/'.$name;
    }
}