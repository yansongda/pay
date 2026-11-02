<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Crypto\Apple\Cryptor;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;

it('解析 ASN.1 并重建 DER', function () {
    // SEQUENCE { INTEGER 1, OCTET STRING "hello" }
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

it('解析 ASN.1 长格式长度', function () {
    // 覆盖 1 字节长形式（0x81）与多字节长形式（0x82）的长度解析与重建
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

it('解析 ASN.1 深度超限', function () {

    $offset = 0;

    // 深度上限 64，直接以超限深度进入递归
    Cryptor::parseAsn1("\x02\x01\x01", $offset, 65);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('解析 ASN.1 截断抛出异常', function (string $der) {

    $offset = 0;

    Cryptor::parseAsn1($der, $offset);
})->with([
        'missing length byte' => ["\x30"],
        'missing long form length bytes' => ["\x30\x82\x01"],
        'length beyond buffer' => ["\x30\x0a\x02\x01\x01"],
        'nested child truncated' => ["\x30\x05\x04\x05abc"],
    ])->throws(InvalidSignException::class, '签名异常: Apple ASN.1 数据截断', Exception::SIGN_ERROR);

it('编码 ASN.1 OID', function () {
    // 1.2.840.113549.1.9.4 → 2A 86 48 86 F7 0D 01 09 04
    expect(Cryptor::encodeAsn1Oid('1.2.840.113549.1.9.4'))->toBe("\x2a\x86\x48\x86\xf7\x0d\x01\x09\x04");

    // 含 >127 的分量（16384 → 81 80 00）
    expect(Cryptor::encodeAsn1Oid('1.2.16384'))->toBe("\x2a\x81\x80\x00");
});

it('PEM 包装与 PEM 转 DER 往返', function () {
    $pem = Cryptor::pemWrap('body', 'PUBLIC KEY');

    expect($pem)->toStartWith("-----BEGIN PUBLIC KEY-----\n");
    expect($pem)->toEndWith("-----END PUBLIC KEY-----\n");
    expect(Cryptor::pemToDer($pem, '不应抛出异常', 'PUBLIC KEY'))->toBe('body');
});

it('PEM 转 DER 标签不匹配抛出异常', function () {

    // 默认 label 为 CERTIFICATE，PUBLIC KEY 块不应被解析为证书
    Cryptor::pemToDer(Cryptor::pemWrap('body', 'PUBLIC KEY'), '配置异常: 解析 Apple 根证书失败');
})->throws(InvalidConfigException::class, '配置异常: 解析 Apple 根证书失败', Exception::CONFIG_CERT_PARSE_FAILED);

it('PEM 转 DER 非法 PEM 抛出异常', function () {

    Cryptor::pemToDer('not a pem', '配置异常: 解析 Apple 支付处理证书失败', 'PUBLIC KEY');
})->throws(InvalidConfigException::class, null, Exception::CONFIG_CERT_PARSE_FAILED);

it('Base64 URL 往返', function () {
    $raw = random_bytes(48);
    $encoded = Cryptor::base64UrlEncode($raw);

    expect($encoded)->not->toContain('+');
    expect($encoded)->not->toContain('/');
    expect($encoded)->not->toContain('=');
    expect(Cryptor::base64UrlDecode($encoded))->toBe($raw);
});

it('非法 Base64 URL 解码抛出异常', function () {

    Cryptor::base64UrlDecode('****');
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('十六进制安全转二进制', function () {
    expect(Cryptor::hexToBinSafe('616263', Exception::PARAMS_APPLE_TOKEN_INVALID, 'msg'))->toBe('abc');
});

it('非法十六进制转二进制抛出异常', function (string $hex) {

    Cryptor::hexToBinSafe($hex, Exception::PARAMS_APPLE_TOKEN_INVALID, '参数异常: hex 非法');
})->with([
        'empty' => [''],
        'odd length' => ['61626'],
        'non hex' => ['zz'],
    ])->throws(InvalidParamsException::class, null, Exception::PARAMS_APPLE_TOKEN_INVALID);

it('DER 转 raw 签名去除整数前缀', function () {
    // r = 00 FF FF FF FF（最高位为 1，DER 内补 0x00），s = 01
    $der = "\x30\x0a\x02\x05\x00\xff\xff\xff\xff\x02\x01\x01";

    $expected = str_pad("\xff\xff\xff\xff", 32, "\x00", STR_PAD_LEFT).str_pad("\x01", 32, "\x00", STR_PAD_LEFT);

    expect(Cryptor::derToRawSignature($der))->toBe($expected);
});

it('DER 转 raw 签名结构非法抛出异常', function () {

    Cryptor::derToRawSignature("\x30\x00");
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('raw 转 DER 签名长度非法抛出异常', function () {

    Cryptor::rawToDerSignature(str_repeat("\x01", 63));
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('ECDSA 签名 raw/DER 往返', function () {
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

it('证书链缺少叶证书抛出异常', function () {

    Cryptor::verifyChain([], 'root-pem', '1.2.840.113635.100.6.2.14', '1.2.840.113635.100.6.29');
})->throws(InvalidSignException::class, '签名异常: Apple 证书链缺少 leaf 证书', Exception::SIGN_ERROR);

it('证书链缺少中间证书抛出异常', function () {
    $leafPem = (string) file_get_contents(cert_path('apple/token-leaf.crt'));

    Cryptor::verifyChain([$leafPem], 'root-pem', '1.2.840.113635.100.6.2.14', '1.2.840.113635.100.6.29');
})->throws(InvalidSignException::class, '签名异常: Apple 证书链缺少 intermediate 证书', Exception::SIGN_ERROR);

it('类为 final 且不可实例化', function () {
    $reflection = new ReflectionClass(Cryptor::class);

    expect($reflection->isFinal())->toBeTrue();
    expect($reflection->getConstructor()?->isPrivate() ?? false)->toBeTrue();
});

