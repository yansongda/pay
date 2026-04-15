<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Supports\Collection;

class AlipayTraitStub
{
    use AlipayTrait;
}

class AlipayTraitTest extends TestCase
{
    public function testVerifyAlipaySignEmptySign(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        AlipayTraitStub::verifyAlipaySign(['alipay_public_cert_path' => 'dummy'], 'contents', '');
    }

    public function testVerifyAlipaySignMissingCert(): void
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_ALIPAY_INVALID);

        AlipayTraitStub::verifyAlipaySign([], 'contents', 'sign');
    }

    public function testVerifyAlipaySignSuccess(): void
    {
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($privateKey, $privatePem);
        $publicKey = openssl_pkey_get_details($privateKey)['key'];

        $contents = 'app_id=2021000000000000&method=alipay.test';
        openssl_sign($contents, $signature, $privatePem, OPENSSL_ALGO_SHA256);

        AlipayTraitStub::verifyAlipaySign([
            'alipay_public_cert_path' => $publicKey,
        ], $contents, base64_encode($signature));

        self::assertTrue(true);
    }

    public function testGetAlipayUrlDefault(): void
    {
        self::assertSame(
            \Yansongda\Pay\Provider\Alipay::URL[Pay::MODE_NORMAL],
            AlipayTraitStub::getAlipayUrl([], null)
        );
    }

    public function testGetAlipayUrlSandbox(): void
    {
        self::assertSame(
            \Yansongda\Pay\Provider\Alipay::URL[Pay::MODE_SANDBOX],
            AlipayTraitStub::getAlipayUrl(['mode' => Pay::MODE_SANDBOX], null)
        );
    }

    public function testGetAlipayUrlWithPayload(): void
    {
        self::assertSame(
            'https://example.com/alipay',
            AlipayTraitStub::getAlipayUrl([], new Collection(['_url' => 'https://example.com/alipay']))
        );
    }
}
