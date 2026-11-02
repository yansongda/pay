<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use RuntimeException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Tests\Stubs\AppleTraitStub;

/**
 * Apple Trait 测试 Helper。
 *
 * 测试侧独立实现 ECDSA DER⇄raw 转换、JWS(ES256) 签名、PEM 证书解析，
 * 与 Trait 内部实现互为对照，避免「用同一份实现验证自己」。
 */
trait AppleTraitHelpers
{
    private function expectTokenInvalidParams(mixed $token): void
    {
        expect(fn () => AppleTraitStub::verifyAppleToken($token, []))
            ->toThrow(InvalidParamsException::class, null, Exception::PARAMS_APPLE_TOKEN_INVALID);
    }

    /**
     * 测试内独立实现：用 jws-leaf.key 对任意 header/payload 签名（反路径专用）。
     *
     * @param array<string, mixed> $header
     * @param array<string, mixed> $payload
     */
    private static function signWithJwsLeaf(array $header, array $payload): string
    {
        $headerB64 = self::b64UrlEncode((string) json_encode($header));
        $payloadB64 = self::b64UrlEncode((string) json_encode($payload));

        $privateKey = openssl_pkey_get_private((string) file_get_contents(self::certPath('jws-leaf.key')));

        if (false === $privateKey) {
            throw new RuntimeException('读取 jws-leaf 私钥失败');
        }

        $derSignature = '';

        if (!openssl_sign($headerB64.'.'.$payloadB64, $derSignature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('ES256 签名失败');
        }

        return $headerB64.'.'.$payloadB64.'.'.self::b64UrlEncode(self::derToRaw($derSignature));
    }

    /**
     * 测试内独立实现：ECDSA DER → raw（r || s，各 32 字节）。
     */
    private static function derToRaw(string $der): string
    {
        $offset = 0;

        if ("\x30" !== ($der[$offset++] ?? '')) {
            throw new RuntimeException('ECDSA DER 签名结构非法');
        }

        $sequenceLength = self::readDerLength($der, $offset);
        $sequenceEnd = $offset + $sequenceLength;
        $r = self::readDerInteger($der, $offset);
        $s = self::readDerInteger($der, $offset);

        if ($sequenceEnd !== $offset) {
            throw new RuntimeException('ECDSA DER 签名结构非法');
        }

        return str_pad($r, 32, "\x00", STR_PAD_LEFT).str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    private static function readDerInteger(string $der, int &$offset): string
    {
        if ("\x02" !== ($der[$offset++] ?? '')) {
            throw new RuntimeException('ECDSA DER INTEGER 非法');
        }

        $length = self::readDerLength($der, $offset);
        $value = substr($der, $offset, $length);
        $offset += $length;

        // 剥前导 0x00（符号位填充，仅当下一字节高位为 1）
        if (1 < strlen($value) && "\x00" === $value[0] && 0 !== (ord($value[1]) & 0x80)) {
            $value = substr($value, 1);
        }

        return $value;
    }

    private static function readDerLength(string $der, int &$offset): int
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

    /**
     * 测试内独立实现：raw（r || s，各 32 字节）→ ECDSA DER。
     */
    private static function rawToDer(string $raw): string
    {
        $r = self::padInteger(substr($raw, 0, 32));
        $s = self::padInteger(substr($raw, 32, 32));

        $sequence = "\x02".chr(strlen($r)).$r."\x02".chr(strlen($s)).$s;

        return "\x30".chr(strlen($sequence)).$sequence;
    }

    private static function padInteger(string $value): string
    {
        $value = ltrim($value, "\x00");

        if ('' === $value) {
            $value = "\x00";
        }

        // 最高位为 1 时补 0x00 防符号位
        if (0 !== (ord($value[0]) & 0x80)) {
            $value = "\x00".$value;
        }

        return $value;
    }

    private static function b64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64UrlDecode(string $data): string
    {
        $padding = (4 - strlen($data) % 4) % 4;

        return (string) base64_decode(strtr($data, '-_', '+/').str_repeat('=', $padding), true);
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

        return (string) base64_decode($base64, true);
    }

    private static function certPath(string $name): string
    {
        return cert_path('apple/'.$name);
    }
}
