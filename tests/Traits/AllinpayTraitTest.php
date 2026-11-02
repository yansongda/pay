<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Support\Traits\AllinpayTraitHelpers;
use Yansongda\Pay\Tests\Stubs\AllinpayTraitStub;

uses(AllinpayTraitHelpers::class);

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
        'mch_secret_key' => cert_path('allinpayPlatformPrivateKey.pem'),
        'allinpay_public_key' => cert_path('allinpayPlatformPublicKey.pem'),
    ]);

    $sign = AllinpayTraitStub::getAllinpaySign($config, $payload);
    expect($sign)->not->toBeEmpty();

    $payload['sign'] = $sign;
    AllinpayTraitStub::verifyAllinpaySign($config, $payload);
    $this->addToAssertionCount(1);
});

it('通联验签为空', function () {

    AllinpayTraitStub::verifyAllinpaySign($this->getConfig(), ['retcode' => 'SUCCESS']);
})->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

it('通联签名缺少密钥', function () {
    $config = new AllinpayConfig([
        'cusid' => '9900000',
        'appid' => '000000',
        'allinpay_public_key' => cert_path('allinpayPlatformPublicKey.pem'),
    ]);

    AllinpayTraitStub::getAllinpaySign($config, ['reqsn' => 'order-1']);
})->throws(InvalidConfigException::class, null, Exception::CONFIG_ALLINPAY_INVALID);

it('通联验签无效', function () {

    AllinpayTraitStub::verifyAllinpaySign($this->getConfig(), [
        'retcode' => 'SUCCESS',
        'sign' => base64_encode('not-a-valid-signature'),
    ]);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('通联验签缺少公钥', function () {
    $config = $this->getConfig();
    $config->setAllinpayPublicKey('');

    AllinpayTraitStub::verifyAllinpaySign($config, [
        'retcode' => 'SUCCESS',
        'sign' => 'abc',
    ]);
})->throws(InvalidConfigException::class, null, Exception::CONFIG_ALLINPAY_INVALID);

it('获取通联 URL 缺少参数', function () {

    AllinpayTraitStub::getAllinpayUrl($this->getConfig(), new Collection());
})->throws(\Yansongda\Artful\Exception\InvalidParamsException::class, null, Exception::PARAMS_ALLINPAY_URL_MISSING);
