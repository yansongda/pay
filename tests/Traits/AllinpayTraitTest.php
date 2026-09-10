<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\AllinpayTrait;
use Yansongda\Supports\Collection;

class AllinpayTraitStub
{
    use AllinpayTrait;
}

class AllinpayTraitTest extends TestCase
{
    public function testGetAllinpayUrl(): void
    {
        $config = $this->getConfig();

        self::assertEquals(
            'https://yansongda.cn',
            AllinpayTraitStub::getAllinpayUrl($config, new Collection(['_url' => 'https://yansongda.cn']))
        );

        $config->setMode(Pay::MODE_SANDBOX);
        self::assertEquals(
            'https://syb-test.allinpay.com/apiweb/tranx/query',
            AllinpayTraitStub::getAllinpayUrl($config, new Collection(['_url' => '/tranx/query']))
        );

        $config->setMode(Pay::MODE_NORMAL);
        self::assertEquals(
            'https://vsp.allinpay.com/apiweb/tranx/query',
            AllinpayTraitStub::getAllinpayUrl($config, new Collection(['_url' => '/tranx/query']))
        );
    }

    public function testGetAllinpaySignContentSkipsEmptyAndInternalKeys(): void
    {
        $content = AllinpayTraitStub::getAllinpaySignContent([
            'b' => '2',
            'a' => '1',
            'sign' => 'should-be-removed',
            '_url' => '/tranx/query',
            'empty' => '',
            'nil' => null,
            'arr' => ['x'],
        ]);

        self::assertSame('a=1&b=2', $content);
    }

    public function testGetAllinpaySignAndVerify(): void
    {
        $payload = [
            'appid' => '000000',
            'cusid' => '9900000',
            'retcode' => 'SUCCESS',
            'retmsg' => 'success',
            'reqsn' => 'order-1',
            'sign' => '',
        ];

        $config = new AllinpayConfig([
            'cusid' => '9900000',
            'appid' => '000000',
            'mch_secret_key' => __DIR__.'/../Cert/allinpayPlatformPrivateKey.pem',
            'allinpay_public_key' => __DIR__.'/../Cert/allinpayPlatformPublicKey.pem',
        ]);

        $sign = AllinpayTraitStub::getAllinpaySign($config, $payload);
        self::assertNotEmpty($sign);

        $payload['sign'] = $sign;
        AllinpayTraitStub::verifyAllinpaySign($config, $payload);
        $this->addToAssertionCount(1);
    }

    public function testVerifyAllinpaySignEmpty(): void
    {
        $this->expectException(InvalidSignException::class);
        $this->expectExceptionCode(Exception::SIGN_EMPTY);

        AllinpayTraitStub::verifyAllinpaySign($this->getConfig(), ['retcode' => 'SUCCESS']);
    }

    public function testVerifyAllinpaySignInvalid(): void
    {
        $this->expectException(InvalidSignException::class);
        $this->expectExceptionCode(Exception::SIGN_ERROR);

        AllinpayTraitStub::verifyAllinpaySign($this->getConfig(), [
            'retcode' => 'SUCCESS',
            'sign' => base64_encode('not-a-valid-signature'),
        ]);
    }

    public function testVerifyAllinpaySignMissingPublicKey(): void
    {
        $config = $this->getConfig();
        $config->setAllinpayPublicKey('');

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionCode(Exception::CONFIG_ALLINPAY_INVALID);

        AllinpayTraitStub::verifyAllinpaySign($config, [
            'retcode' => 'SUCCESS',
            'sign' => 'abc',
        ]);
    }

    public function testGetAllinpayUrlMissing(): void
    {
        $this->expectException(\Yansongda\Artful\Exception\InvalidParamsException::class);
        $this->expectExceptionCode(Exception::PARAMS_ALLINPAY_URL_MISSING);

        AllinpayTraitStub::getAllinpayUrl($this->getConfig(), new Collection());
    }

    private function getConfig(): AllinpayConfig
    {
        $config = AllinpayTraitStub::getProviderConfig('allinpay', []);

        self::assertInstanceOf(AllinpayConfig::class, $config);

        return $config;
    }
}
