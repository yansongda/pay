<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\AllinpayTrait;
use Yansongda\Supports\Collection;

class AllinpayTraitStub
{
use AllinpayTrait;
}

uses(AllinpayTraitTestHelpers::class);

it('获取通联 URL', function () {
    $config = $this->getConfig();

    expect(AllinpayTraitStub::getAllinpayUrl($config, new Collection(['_url' => 'https://yansongda.cn'])))->toEqual('https://yansongda.cn');

    $config->setMode(PayMode::SANDBOX);
    expect(AllinpayTraitStub::getAllinpayUrl($config, new Collection(['_url' => '/tranx/query'])))->toEqual('https://syb-test.allinpay.com/apiweb/tranx/query');

    $config->setMode(PayMode::NORMAL);
    expect(AllinpayTraitStub::getAllinpayUrl($config, new Collection(['_url' => '/tranx/query'])))->toEqual('https://vsp.allinpay.com/apiweb/tranx/query');
});

it('通联待签名内容跳过空值与内部键', function () {
    $content = AllinpayTraitStub::getAllinpaySignContent([
        'b' => '2',
        'a' => '1',
        'sign' => 'should-be-removed',
        '_url' => '/tranx/query',
        'empty' => '',
        'nil' => null,
        'arr' => ['x'],
    ]);

    expect($content)->toBe('a=1&b=2');
});

it('通联签名与验签', function () {
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
    expect($sign)->not->toBeEmpty();

    $payload['sign'] = $sign;
    AllinpayTraitStub::verifyAllinpaySign($config, $payload);
    $this->addToAssertionCount(1);
});

it('通联验签为空', function () {
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);

    AllinpayTraitStub::verifyAllinpaySign($this->getConfig(), ['retcode' => 'SUCCESS']);
});

it('通联签名缺少密钥', function () {
    $config = new AllinpayConfig([
        'cusid' => '9900000',
        'appid' => '000000',
        'allinpay_public_key' => __DIR__.'/../Cert/allinpayPlatformPublicKey.pem',
    ]);

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_ALLINPAY_INVALID);

    AllinpayTraitStub::getAllinpaySign($config, ['reqsn' => 'order-1']);
});

it('通联验签无效', function () {
    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AllinpayTraitStub::verifyAllinpaySign($this->getConfig(), [
        'retcode' => 'SUCCESS',
        'sign' => base64_encode('not-a-valid-signature'),
    ]);
});

it('通联验签缺少公钥', function () {
    $config = $this->getConfig();
    $config->setAllinpayPublicKey('');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_ALLINPAY_INVALID);

    AllinpayTraitStub::verifyAllinpaySign($config, [
        'retcode' => 'SUCCESS',
        'sign' => 'abc',
    ]);
});

it('获取通联 URL 缺少参数', function () {
    $this->expectException(\Yansongda\Artful\Exception\InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_ALLINPAY_URL_MISSING);

    AllinpayTraitStub::getAllinpayUrl($this->getConfig(), new Collection());
});

trait AllinpayTraitTestHelpers
{
    private function getConfig(): AllinpayConfig
    {
        $config = AllinpayTraitStub::getProviderConfig('allinpay', []);
    
        self::assertInstanceOf(AllinpayConfig::class, $config);
    
        return $config;
    }
}
