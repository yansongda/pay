<?php

declare(strict_types=1);

namespace Yansongda\Pay\Crypto\Apple;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;

/**
 * Apple 通用密码学工具（无业务语义）。
 *
 * - ASN.1 DER 解析 / 重建 / 长度与 OID 编码
 * - PEM ↔ DER、base64url、hex 编解码
 * - ECDSA 签名 raw ↔ DER 互换
 * - 证书链逐级验证与有效期检查
 *
 * 支付令牌域（PKCS#7 / ECDH-KDF / AES-GCM）见 TokenVerifier，通知域（JWS）见 JwsVerifier。
 */
final class Cryptor
{
    /** ASN.1 解析的最大嵌套深度（防恶意深嵌套 DER 导致递归栈溢出） */
    private const ASN1_MAX_DEPTH = 64;

    private function __construct() {}

    /**
     * 解析 ASN.1 DER 节点。
     *
     * 返回节点的 tag、原始内容切片与子节点；子节点内容为原 DER 的完整切片，
     * 因此 reConstructDer() 可自包含地重建节点完整 DER（规避相对 offset 错位）。
     *
     * @return array{tag: int, value: string, children: array<int, array{tag: int, value: string, children: array<int, mixed>}>}
     *
     * @throws InvalidSignException
     */
    public static function parseAsn1(string $der, int &$offset = 0, int $depth = 0): array
    {
        if ($depth > self::ASN1_MAX_DEPTH) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple ASN.1 嵌套深度超限');
        }

        $tag = ord($der[$offset++]);
        $lengthByte = ord($der[$offset++]);
        $length = $lengthByte;

        if (0 !== ($lengthByte & 0x80)) {
            $bytesCount = $lengthByte & 0x7F;
            $length = 0;

            for ($i = 0; $i < $bytesCount; ++$i) {
                $length = ($length << 8) | ord($der[$offset++]);
            }
        }

        $value = substr($der, $offset, $length);
        $offset += $length;

        $children = [];

        if (0 !== ($tag & 0x20)) {
            $childOffset = 0;
            $valueLength = strlen($value);

            while ($childOffset < $valueLength) {
                $children[] = self::parseAsn1($value, $childOffset, $depth + 1);
            }
        }

        return ['tag' => $tag, 'value' => $value, 'children' => $children];
    }

    /**
     * 还原节点的完整 DER 编码（tag + length + value）。
     *
     * @param array{tag: int, value: string, children: array<int, array{tag: int, value: string, children: array<int, mixed>}>} $node
     */
    public static function reConstructDer(array $node): string
    {
        if ([] !== $node['children']) {
            $parts = [];

            foreach ($node['children'] as $child) {
                $parts[] = self::reConstructDer($child);
            }

            $node['value'] = implode('', $parts);
        }

        return chr($node['tag']).self::asn1LengthEncode(strlen($node['value'])).$node['value'];
    }

    /**
     * 把点分 OID 编码为 DER 内容字节，用于与 PKCS#7 属性 OID 直接比较。
     */
    public static function encodeAsn1Oid(string $oid): string
    {
        $parts = array_map('intval', explode('.', $oid));
        $first = array_shift($parts);
        $second = array_shift($parts) ?? 0;
        $der = chr($first * 40 + $second);

        foreach ($parts as $part) {
            $encoded = chr($part & 0x7F);
            $part >>= 7;

            while (0 < $part) {
                $encoded = chr(0x80 | ($part & 0x7F)).$encoded;
                $part >>= 7;
            }

            $der .= $encoded;
        }

        return $der;
    }

    /**
     * DER 内容包装为 PEM（如 label 取 CERTIFICATE / PUBLIC KEY）。
     */
    public static function pemWrap(string $der, string $label): string
    {
        return '-----BEGIN '.$label.'-----'."\n".chunk_split(base64_encode($der), 64, "\n").'-----END '.$label.'-----'."\n";
    }

    /**
     * 从 PEM 提取 DER 正文（按 BEGIN/END 行扫描，不依赖行数偏移）。
     *
     * 证书与公钥共用同一实现，差异仅在 label 与错误文案，避免两处解析逻辑漂移。
     *
     * @throws InvalidConfigException
     */
    public static function pemToDer(string $pem, string $errorMessage, string $label = 'CERTIFICATE'): string
    {
        $lines = preg_split('/\r?\n/', $pem);
        $base64 = '';
        $inBlock = false;

        foreach (false === $lines ? [] : $lines as $line) {
            $line = trim($line);

            if ('-----BEGIN '.$label.'-----' === $line) {
                $inBlock = true;

                continue;
            }

            if ('-----END '.$label.'-----' === $line) {
                break;
            }

            if ($inBlock) {
                $base64 .= $line;
            }
        }

        $der = '' === $base64 ? false : base64_decode($base64, true);

        if (false === $der || '' === $der) {
            throw new InvalidConfigException(Exception::CONFIG_CERT_PARSE_FAILED, $errorMessage);
        }

        return $der;
    }

    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @throws InvalidSignException
     */
    public static function base64UrlDecode(string $data): string
    {
        $padding = (4 - strlen($data) % 4) % 4;
        $decoded = base64_decode(strtr($data, '-_', '+/').str_repeat('=', $padding), true);

        if (false === $decoded) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS base64url 解码失败');
        }

        return $decoded;
    }

    /**
     * 十六进制字符串安全解码（防 PHP 8 hex2bin() ValueError）。
     *
     * @throws InvalidParamsException
     */
    public static function hexToBinSafe(string $hex, int $errorCode, string $message): string
    {
        if ('' === $hex || !ctype_xdigit($hex) || 0 !== strlen($hex) % 2) {
            throw new InvalidParamsException($errorCode, $message);
        }

        $bin = hex2bin($hex);

        if (false === $bin) {
            throw new InvalidParamsException($errorCode, $message);
        }

        return $bin;
    }

    /**
     * JWS 的 raw ECDSA 签名（r || s，各 32 字节）→ DER（与 derToRawSignature() 对称）。
     *
     * @throws InvalidSignException
     */
    public static function rawToDerSignature(string $raw): string
    {
        if (64 !== strlen($raw)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple JWS 签名长度非法');
        }

        $r = ltrim(substr($raw, 0, 32), "\x00");
        $s = ltrim(substr($raw, 32, 32), "\x00");

        if ('' === $r) {
            $r = "\x00";
        }

        if ('' === $s) {
            $s = "\x00";
        }

        // 最高位为 1 时补 0x00（符号位填充），见 RFC 3279 / SEC1
        if (0 !== (ord($r[0]) & 0x80)) {
            $r = "\x00".$r;
        }

        if (0 !== (ord($s[0]) & 0x80)) {
            $s = "\x00".$s;
        }

        $sequence = "\x02".chr(strlen($r)).$r."\x02".chr(strlen($s)).$s;

        return "\x30".chr(strlen($sequence)).$sequence;
    }

    /**
     * ECDSA DER 签名转 raw 格式（r || s，各 32 字节）。
     *
     * @throws InvalidSignException
     */
    public static function derToRawSignature(string $derSignature): string
    {
        $sequence = self::parseAsn1($derSignature);

        if (0x30 !== $sequence['tag'] || 2 !== count($sequence['children'])
            || 0x02 !== $sequence['children'][0]['tag']
            || 0x02 !== $sequence['children'][1]['tag']
        ) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: ECDSA DER 签名结构非法');
        }

        $r = self::stripIntegerPrefix($sequence['children'][0]['value']);
        $s = self::stripIntegerPrefix($sequence['children'][1]['value']);

        return str_pad($r, 32, "\x00", STR_PAD_LEFT).str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    /**
     * 按 OID 定位 leaf/intermediate 证书并逐级验证证书链，返回 leaf 证书 PEM。
     *
     * @param array<int, string> $certs 证书 PEM 列表（PKCS#7 内全部证书 + 配置兜底 intermediate）
     *
     * @throws InvalidSignException
     */
    public static function verifyChain(array $certs, string $rootPem, string $intermediateOid, string $leafOid): string
    {
        $leafPem = null;
        $intermediatePem = null;

        foreach ($certs as $pem) {
            $info = openssl_x509_parse($pem);

            if (false === $info) {
                continue;
            }

            $extensions = $info['extensions'] ?? [];

            if (null === $leafPem && isset($extensions[$leafOid])) {
                self::assertCertNotExpired($info, 'leaf');
                $leafPem = $pem;
            }

            if (null === $intermediatePem && isset($extensions[$intermediateOid])) {
                self::assertCertNotExpired($info, 'intermediate');
                $intermediatePem = $pem;
            }
        }

        if (null === $leafPem) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 证书链缺少 leaf 证书');
        }

        if (null === $intermediatePem) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 证书链缺少 intermediate 证书（可配置 -- [apple_intermediate_ca] 兜底）');
        }

        $rootInfo = openssl_x509_parse($rootPem);

        if (false !== $rootInfo) {
            self::assertCertNotExpired($rootInfo, 'root');
        }

        if (1 !== openssl_x509_verify($leafPem, $intermediatePem)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 证书链验证失败（leaf ← intermediate）');
        }

        if (1 !== openssl_x509_verify($intermediatePem, $rootPem)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple 证书链验证失败（intermediate ← root）');
        }

        return $leafPem;
    }

    /**
     * 校验证书有效期（对齐官方库 ChainVerifier 的时间窗口检查）。
     *
     * @param array<string, mixed> $certInfo openssl_x509_parse 的解析结果
     *
     * @throws InvalidSignException
     */
    public static function assertCertNotExpired(array $certInfo, string $role): void
    {
        $now = time();

        if (!isset($certInfo['validFrom_time_t'], $certInfo['validTo_time_t'])
            || $now < (int) $certInfo['validFrom_time_t'] || $now > (int) $certInfo['validTo_time_t']) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Apple '.$role.' 证书不在有效期内');
        }
    }

    private static function asn1LengthEncode(int $length): string
    {
        if (0x80 > $length) {
            return chr($length);
        }

        $encoded = '';

        while (0 < $length) {
            $encoded = chr($length & 0xFF).$encoded;
            $length >>= 8;
        }

        return chr(0x80 | strlen($encoded)).$encoded;
    }

    /**
     * 剥 INTEGER 前导 0x00（符号位填充），仅当下一字节高位为 1 时剥除。
     */
    private static function stripIntegerPrefix(string $value): string
    {
        if (strlen($value) > 1 && "\x00" === $value[0] && 0 !== (ord($value[1]) & 0x80)) {
            return substr($value, 1);
        }

        return $value;
    }
}
