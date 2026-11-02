<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Crypto\Apple\Cryptor;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;

it('parse asn1 and re construct der', function () {
    $der = "\x30\x0a\x02\x01\x01\x04\x05hello";

    $node = Cryptor::parseAsn1($der);

    expect($node['tag'])->toBe(0x30);
    expect($node['children'])->toHaveCount(2);
    expect($node['children'][0]['tag'])->toBe(0x02);
    expect($node['children'][0]['value'])->toBe("\x01");
    expect($node['children'][1]['tag'])->toBe(0x04);
    expect($node['children'][1]['value'])->toBe('hello');

    // 子节点保留完整切片，重建后与原 DER 一致
    expect(Cryptor::reConstructDer($node))->toBe($der);
});

it('parse asn1 long form length', function () {
    $cases = [
        200 => "\x30\x81\xcb\x04\x81\xc8".str_repeat('a', 200),
        300 => "\x30\x82\x01\x30\x04\x82\x01\x2c".str_repeat('a', 300),
    ];

    foreach ($cases as $size => $der) {
        $node = Cryptor::parseAsn1($der);

        expect(strlen($node['children'][0]['value']))->toBe($size);
        expect(Cryptor::reConstructDer($node))->toBe($der);
    }
});

it('parse asn1 depth limit exceeded', function () {
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    $offset = 0;

    // 深度上限 64，直接以超限深度进入递归
    Cryptor::parseAsn1("\x02\x01\x01", $offset, 65);
});

it('parse asn1 truncated throws exception', function (string $der) {
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);
    $this->expectExceptionMessage('签名异常: Apple ASN.1 数据截断');

    $offset = 0;

    Cryptor::parseAsn1($der, $offset);
})->with([
        'missing length byte' => ["\x30"],
        'missing long form length bytes' => ["\x30\x82\x01"],
        'length beyond buffer' => ["\x30\x0a\x02\x01\x01"],
        'nested child truncated' => ["\x30\x05\x04\x05abc"],
    ]);

it('encode asn1 oid', function () {
    expect(Cryptor::encodeAsn1Oid('1.2.840.113549.1.9.4'))->toBe("\x2a\x86\x48\x86\xf7\x0d\x01\x09\x04");

    // 含 >127 的分量（16384 → 81 80 00）
    expect(Cryptor::encodeAsn1Oid('1.2.16384'))->toBe("\x2a\x81\x80\x00");
});

it('pem wrap and pem to der round trip', function () {
    $pem = Cryptor::pemWrap('body', 'PUBLIC KEY');

    expect($pem)->toStartWith("-----BEGIN PUBLIC KEY-----\n");
    expect($pem)->toEndWith("-----END PUBLIC KEY-----\n");
    expect(Cryptor::pemToDer($pem, '不应抛出异常', 'PUBLIC KEY'))->toBe('body');
});

it('pem to der with label mismatch throws exception', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_CERT_PARSE_FAILED);
    $this->expectExceptionMessage('配置异常: 解析 Apple 根证书失败');

    // 默认 label 为 CERTIFICATE，PUBLIC KEY 块不应被解析为证书
    Cryptor::pemToDer(Cryptor::pemWrap('body', 'PUBLIC KEY'), '配置异常: 解析 Apple 根证书失败');
});

it('pem to der with invalid pem throws exception', function () {
    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_CERT_PARSE_FAILED);

    Cryptor::pemToDer('not a pem', '配置异常: 解析 Apple 支付处理证书失败', 'PUBLIC KEY');
});

it('base64 url round trip', function () {
    $raw = random_bytes(48);
    $encoded = Cryptor::base64UrlEncode($raw);

    expect($encoded)->not->toContain('+');
    expect($encoded)->not->toContain('/');
    expect($encoded)->not->toContain('=');
    expect(Cryptor::base64UrlDecode($encoded))->toBe($raw);
});

it('base64 url decode invalid throws exception', function () {
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    Cryptor::base64UrlDecode('****');
});

it('hex to bin safe', function () {
    expect(Cryptor::hexToBinSafe('616263', Exception::PARAMS_APPLE_TOKEN_INVALID, 'msg'))->toBe('abc');
});

it('hex to bin safe invalid throws exception', function (string $hex) {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_APPLE_TOKEN_INVALID);

    Cryptor::hexToBinSafe($hex, Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: hex 非法');
})->with([
        'empty' => [''],
        'odd length' => ['61626'],
        'non hex' => ['zz'],
    ]);

it('der to raw signature strips integer prefix', function () {
    $der = "\x30\x0a\x02\x05\x00\xff\xff\xff\xff\x02\x01\x01";

    $expected = str_pad("\xff\xff\xff\xff", 32, "\x00", STR_PAD_LEFT).str_pad("\x01", 32, "\x00", STR_PAD_LEFT);

    expect(Cryptor::derToRawSignature($der))->toBe($expected);
});

it('der to raw signature invalid structure throws exception', function () {
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    Cryptor::derToRawSignature("\x30\x00");
});

it('raw to der signature invalid length throws exception', function () {
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    Cryptor::rawToDerSignature(str_repeat("\x01", 63));
});

it('ecdsa signature raw der round trip', function () {
    $privateKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
    expect($privateKey)->not->toBeFalse();

    expect(openssl_sign('apple', $derSignature, $privateKey, OPENSSL_ALGO_SHA256))->toBeTrue();

    $raw = Cryptor::derToRawSignature($derSignature);
    expect(strlen($raw))->toBe(64);

    $details = openssl_pkey_get_details($privateKey);
    expect($details)->toBeArray();
    expect($details['key'])->toBeString();

    expect(openssl_verify('apple', Cryptor::rawToDerSignature($raw), $details['key'], OPENSSL_ALGO_SHA256))->toBe(1);
});

it('verify chain missing leaf throws exception', function () {
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);
    $this->expectExceptionMessage('签名异常: Apple 证书链缺少 leaf 证书');

    Cryptor::verifyChain([], 'root-pem', '1.2.840.113635.100.6.2.14', '1.2.840.113635.100.6.29');
});

it('verify chain missing intermediate throws exception', function () {
    $leafPem = (string) file_get_contents(__DIR__.'/../../Cert/apple/token-leaf.crt');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);
    $this->expectExceptionMessage('签名异常: Apple 证书链缺少 intermediate 证书');

    Cryptor::verifyChain([$leafPem], 'root-pem', '1.2.840.113635.100.6.2.14', '1.2.840.113635.100.6.29');
});

it('class is final and not instantiable', function () {
    $reflection = new ReflectionClass(Cryptor::class);

    expect($reflection->isFinal())->toBeTrue();
    expect($reflection->getConstructor()?->isPrivate() ?? false)->toBeTrue();
});

