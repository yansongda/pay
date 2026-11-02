<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\JsapiInvokePlugin;
use Yansongda\Supports\Collection;

it('missing prepay id', function () {
    $plugin = new JsapiInvokePlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_MISSING_NECESSARY_PARAMS);
    $this->expectExceptionMessage('预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
    $plugin = new JsapiInvokePlugin();
    $rocket = (new Rocket())
        ->setDestination(new Collection(['prepay_id' => 'yansongda']))
        ->setPayload(['_invoke_appid' => '111']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->all())->toHaveKey('paySign');
    expect($contents->all())->toHaveKey('timeStamp');
    expect($contents->all())->toHaveKey('nonceStr');
    expect($contents->get('appId'))->toEqual('111');
    expect($contents->get('package'))->toEqual('prepay_id=yansongda');
    expect($contents->get('signType'))->toEqual('RSA');
});

it('normal', function () {
    $plugin = new JsapiInvokePlugin();
    $rocket = (new Rocket())->setDestination(new Collection(['prepay_id' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->all())->toHaveKey('paySign');
    expect($contents->all())->toHaveKey('timeStamp');
    expect($contents->all())->toHaveKey('nonceStr');
    expect($contents->get('appId'))->toEqual('wx55955316af4ef13');
    expect($contents->get('package'))->toEqual('prepay_id=yansongda');
    expect($contents->get('signType'))->toEqual('RSA');
});

it('service params', function () {
    $plugin = new JsapiInvokePlugin();
    $rocket = (new Rocket())
        ->setParams(['_config' => 'service_provider4'])
        ->setDestination(new Collection(['prepay_id' => 'yansongda']))
        ->setPayload(['_invoke_appid' => '111']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->all())->toHaveKey('paySign');
    expect($contents->all())->toHaveKey('timeStamp');
    expect($contents->all())->toHaveKey('nonceStr');
    expect($contents->get('appId'))->toEqual('111');
    expect($contents->get('package'))->toEqual('prepay_id=yansongda');
    expect($contents->get('signType'))->toEqual('RSA');
});

it('service', function () {
    $plugin = new JsapiInvokePlugin();
    $rocket = (new Rocket())
        ->setParams(['_config' => 'service_provider'])
        ->setDestination(new Collection(['prepay_id' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->all())->toHaveKey('paySign');
    expect($contents->all())->toHaveKey('timeStamp');
    expect($contents->all())->toHaveKey('nonceStr');
    expect($contents->get('appId'))->toEqual('wx55955316af4ef15');
    expect($contents->get('package'))->toEqual('prepay_id=yansongda');
    expect($contents->get('signType'))->toEqual('RSA');
});

