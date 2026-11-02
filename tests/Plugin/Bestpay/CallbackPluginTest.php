<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Bestpay\V1\CallbackPlugin;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Support\Traits\BestpayCallbackHelpers;

uses(BestpayTrait::class);
uses(BestpayCallbackHelpers::class);

it('有效回调', function () {
    $data = [
        'institutionCode' => '3178033925245778',
        'merchantNo' => '3178033925245778',
        'outTradeNo' => 'ORDER001',
        'notifyType' => 'PAY',
        'totalAmt' => '99',
        'tradeStatus' => 'SUCCESS',
        'tradeNo' => 'TRADE001',
    ];

    $data['sign'] = self::signBestpayContent($this->defaultConfig(), self::getBestpaySignContent($data));

    $rocket = new Rocket();
    $rocket->setParams(['_request' => new Collection($data), '_params' => []]);

    $result = (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);

    expect($result->getDestination()->get('tradeStatus'))->toEqual('SUCCESS');
    expect($result->getDestination()->get('outTradeNo'))->toEqual('ORDER001');
});

it('含空字段的有效回调', function () {
    // 回调报文含 null / 空串字段时，拼串为 k=null / k=（对齐官方 AssembleSignatureData）
    $data = [
        'institutionCode' => '3178033925245778',
        'merchantNo' => '3178033925245778',
        'outTradeNo' => 'ORDER001',
        'notifyType' => 'REFUND',
        'outRefundNo' => 'REFUND001',
        'refundAmt' => '99',
        'remark' => '',
        'payFinishedDate' => null,
        'tradeStatus' => 'SUCCESS',
    ];

    $data['sign'] = self::signBestpayContent($this->defaultConfig(), self::getBestpaySignContent($data));

    $rocket = new Rocket();
    $rocket->setParams(['_request' => new Collection($data), '_params' => []]);

    $result = (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);

    expect($result->getDestination()->get('tradeStatus'))->toEqual('SUCCESS');
});

it('签名非法', function () {

    $data = [
        'outTradeNo' => 'ORDER001',
        'tradeStatus' => 'SUCCESS',
        'sign' => base64_encode('invalid-sign'),
    ];

    $rocket = new Rocket();
    $rocket->setParams(['_request' => new Collection($data), '_params' => []]);

    (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);
})->throws(InvalidSignException::class);

it('租户配置回调', function () {
    $data = [
        'institutionCode' => '3178033925245779',
        'merchantNo' => '3178033925245779',
        'outTradeNo' => 'ORDER002',
        'notifyType' => 'PAY',
        'totalAmt' => '99',
        'tradeStatus' => 'SUCCESS',
    ];

    // 用 second 租户的证书加签
    $data['sign'] = self::signBestpayContent($this->secondConfig(), self::getBestpaySignContent($data));

    $rocket = new Rocket();
    $rocket->setParams(['_request' => new Collection($data), '_params' => ['_config' => 'second']]);

    $result = (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);

    expect($result->getDestination()->get('outTradeNo'))->toEqual('ORDER002');
});

it('回调租户隔离', function () {
    // 用 default 证书加签，但路由到 second 租户（不同平台公钥）→ 验签必须失败

    $data = [
        'merchantNo' => '3178033925245778',
        'outTradeNo' => 'ORDER003',
        'tradeStatus' => 'SUCCESS',
    ];

    $data['sign'] = self::signBestpayContent($this->defaultConfig(), self::getBestpaySignContent($data));

    $rocket = new Rocket();
    $rocket->setParams(['_request' => new Collection($data), '_params' => ['_config' => 'second']]);

    (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);
})->throws(InvalidSignException::class);

it('缺少请求对象', function () {

    $rocket = new Rocket();
    $rocket->setParams([]);

    (new CallbackPlugin())->assembly($rocket, fn ($r) => $r);
})->throws(\Yansongda\Artful\Exception\InvalidParamsException::class);
