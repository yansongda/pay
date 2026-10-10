<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Crypto\Apple;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Crypto\Apple\Cryptor;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Tests\TestCase;

class CryptorTest extends TestCase
{
    public function testParseAsn1AndReConstructDer(): void
    {
        // SEQUENCE { INTEGER 1, OCTET STRING "hello" }
        $der = "\x30\x0a\x02\x01\x01\x04\x05hello";

        $node = Cryptor::parseAsn1($der);

        self::assertSame(0x30, $node['tag']);
        self::assertCount(2, $node['children']);
        self::assertSame(0x02, $node['children'][0]['tag']);
        self::assertSame("\x01", $node['children'][0]['value']);
        self::assertSame(0x04, $node['children'][1]['tag']);
        self::assertSame('hello', $node['children'][1]['value']);

        // 子节点保留完整切片，重建后与原 DER 一致
        self::assertSame($der, Cryptor::reConstructDer($node));
    }

    public function testParseAsn1LongFormLength(): void
    {
        // 覆盖 1 字节长形式（0x81）与多字节长形式（0x82）的长度解析与重建
        $cases = [
            200 => "\x30\x81\xcb\x04\x81\xc8".str_repeat('a', 200),
            300 => "\x30\x82\x01\x30\x04\x82\x01\x2c".str_repeat('a', 300),
        ];

        foreach ($cases as $size => $der) {
            $node = Cryptor::parseAsn1($der);

            self::assertSame($size, strlen($node['children'][0]['value']));
            self::assertSame($der, Cryptor::reConstructDer($node));
        }
    }

    public function testParseAsn1DepthLimitExceeded(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $offset = 0;

        // 深度上限 64，直接以超限深度进入递归
        Cryptor::parseAsn1("\x02\x01\x01", $offset, 65);
    }

    public function testEncodeAsn1Oid(): void
    {
        // 1.2.840.113549.1.9.4 → 2A 86 48 86 F7 0D 01 09 04
        self::assertSame("\x2a\x86\x48\x86\xf7\x0d\x01\x09\x04", Cryptor::encodeAsn1Oid('1.2.840.113549.1.9.4'));

        // 含 >127 的分量（16384 → 81 80 00）
        self::assertSame("\x2a\x81\x80\x00", Cryptor::encodeAsn1Oid('1.2.16384'));
    }

    public function testPemWrapAndPemToDerRoundTrip(): void
    {
        $pem = Cryptor::pemWrap('body', 'PUBLIC KEY');

        self::assertStringStartsWith("-----BEGIN PUBLIC KEY-----\n", $pem);
        self::assertStringEndsWith("-----END PUBLIC KEY-----\n", $pem);
        self::assertSame('body', Cryptor::pemToDer($pem, '不应抛出异常', 'PUBLIC KEY'));
    }

    public function testPemToDerWithLabelMismatchThrowsException(): void
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_CERT_PARSE_FAILED);
        self::expectExceptionMessage('配置异常: 解析 Apple 根证书失败');

        // 默认 label 为 CERTIFICATE，PUBLIC KEY 块不应被解析为证书
        Cryptor::pemToDer(Cryptor::pemWrap('body', 'PUBLIC KEY'), '配置异常: 解析 Apple 根证书失败');
    }

    public function testPemToDerWithInvalidPemThrowsException(): void
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_CERT_PARSE_FAILED);

        Cryptor::pemToDer('not a pem', '配置异常: 解析 Apple 支付处理证书失败', 'PUBLIC KEY');
    }

    public function testBase64UrlRoundTrip(): void
    {
        $raw = random_bytes(48);
        $encoded = Cryptor::base64UrlEncode($raw);

        self::assertStringNotContainsString('+', $encoded);
        self::assertStringNotContainsString('/', $encoded);
        self::assertStringNotContainsString('=', $encoded);
        self::assertSame($raw, Cryptor::base64UrlDecode($encoded));
    }

    public function testBase64UrlDecodeInvalidThrowsException(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        Cryptor::base64UrlDecode('****');
    }

    public function testHexToBinSafe(): void
    {
        self::assertSame('abc', Cryptor::hexToBinSafe('616263', Exception::PARAMS_APPLE_TOKEN_INVALID, 'msg'));
    }

    #[DataProvider('provideInvalidHex')]
    public function testHexToBinSafeInvalidThrowsException(string $hex): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_APPLE_TOKEN_INVALID);

        Cryptor::hexToBinSafe($hex, Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: hex 非法');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideInvalidHex(): array
    {
        return [
            'empty' => [''],
            'odd length' => ['61626'],
            'non hex' => ['zz'],
        ];
    }

    public function testDerToRawSignatureStripsIntegerPrefix(): void
    {
        // r = 00 FF FF FF FF（最高位为 1，DER 内补 0x00），s = 01
        $der = "\x30\x0a\x02\x05\x00\xff\xff\xff\xff\x02\x01\x01";

        $expected = str_pad("\xff\xff\xff\xff", 32, "\x00", STR_PAD_LEFT).str_pad("\x01", 32, "\x00", STR_PAD_LEFT);

        self::assertSame($expected, Cryptor::derToRawSignature($der));
    }

    public function testDerToRawSignatureInvalidStructureThrowsException(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        Cryptor::derToRawSignature("\x30\x00");
    }

    public function testRawToDerSignatureInvalidLengthThrowsException(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        Cryptor::rawToDerSignature(str_repeat("\x01", 63));
    }

    public function testEcdsaSignatureRawDerRoundTrip(): void
    {
        $privateKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        self::assertNotFalse($privateKey);

        self::assertTrue(openssl_sign('apple', $derSignature, $privateKey, OPENSSL_ALGO_SHA256));

        $raw = Cryptor::derToRawSignature($derSignature);
        self::assertSame(64, strlen($raw));

        $details = openssl_pkey_get_details($privateKey);
        self::assertIsArray($details);
        self::assertIsString($details['key']);

        self::assertSame(1, openssl_verify('apple', Cryptor::rawToDerSignature($raw), $details['key'], OPENSSL_ALGO_SHA256));
    }

    public function testVerifyChainMissingLeafThrowsException(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);
        self::expectExceptionMessage('签名异常: Apple 证书链缺少 leaf 证书');

        Cryptor::verifyChain([], 'root-pem', '1.2.840.113635.100.6.2.14', '1.2.840.113635.100.6.29');
    }

    public function testVerifyChainMissingIntermediateThrowsException(): void
    {
        $leafPem = (string) file_get_contents(__DIR__.'/../../Cert/apple/token-leaf.crt');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);
        self::expectExceptionMessage('签名异常: Apple 证书链缺少 intermediate 证书');

        Cryptor::verifyChain([$leafPem], 'root-pem', '1.2.840.113635.100.6.2.14', '1.2.840.113635.100.6.29');
    }

    public function testClassIsFinalAndNotInstantiable(): void
    {
        $reflection = new ReflectionClass(Cryptor::class);

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->getConstructor()?->isPrivate() ?? false);
    }
}
