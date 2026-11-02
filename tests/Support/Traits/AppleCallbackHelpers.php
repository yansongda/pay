<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

use RuntimeException;

/**
 * Apple 支付回调 Helper。
 *
 * 测试侧独立实现 ES256 JWS 签名（jws-leaf.key）与 DER→raw 转换，
 * 供 CallbackPlugin 正/反路径构造二级 JWS 使用。
 */
trait AppleCallbackHelpers
{
    /**
     * 构造无 x5c 的二级 JWS（signedTransactionInfo/signedRenewalInfo）——反路径专用：
     * 对齐官方库后，二级 JWS 须自带 x5c，此形态应被拒绝。
     *
     * @param array<string, mixed> $payload
     */
    private static function makeInnerJws(array $payload): string
    {
        $headerB64 = self::base64UrlEncode((string) json_encode(['alg' => 'ES256']));
        $payloadB64 = self::base64UrlEncode((string) json_encode($payload));

        $privateKey = openssl_pkey_get_private((string) file_get_contents(cert_path('apple/jws-leaf.key')));

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
     * 篡改 JWS 的 payload 段（保持 header/签名不变，用于反路径用例）。
     *
     * @param array<string, mixed> $payload
     */
    private static function tamperJwsPayload(string $jws, array $payload): string
    {
        $parts = explode('.', $jws);
        $parts[1] = self::base64UrlEncode((string) json_encode($payload));

        return implode('.', $parts);
    }

    private static function derToRaw(string $der): string
    {
        $offset = 2;
        $parts = [];

        for ($i = 0; $i < 2; ++$i) {
            if ("\x02" !== ($der[$offset] ?? '')) {
                throw new RuntimeException('ECDSA DER 签名结构非法');
            }

            $length = ord($der[++$offset]);
            ++$offset;
            $value = substr($der, $offset, $length);
            $offset += $length;

            // 剥前导 0x00（符号位填充，仅当下一字节高位为 1，DER 最小编码保证可安全剥除）
            if (1 < strlen($value) && "\x00" === $value[0]) {
                $value = substr($value, 1);
            }

            $parts[] = $value;
        }

        return str_pad((string) $parts[0], 32, "\x00", STR_PAD_LEFT).str_pad((string) $parts[1], 32, "\x00", STR_PAD_LEFT);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
