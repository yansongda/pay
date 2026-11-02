<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\AppInvokePlugin;
use Yansongda\Supports\Collection;

it('缺少 prepay_id', function () {
    $plugin = new AppInvokePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidResponseException::class, '预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求', Exception::RESPONSE_MISSING_NECESSARY_PARAMS);

it('普通参数', function () {
    $plugin = new AppInvokePlugin();
    $rocket = (new Rocket())
        ->setDestination(new Collection(['prepay_id' => 'yansongda']))
        ->setPayload(['_invoke_appid' => '111', '_invoke_partnerid' => '222']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->all())->toHaveKey('sign');
    expect($contents->all())->toHaveKey('timestamp');
    expect($contents->all())->toHaveKey('noncestr');
    expect($contents->get('appid'))->toEqual('111');
    expect($contents->get('package'))->toEqual('Sign=WXPay');
    expect($contents->get('partnerid'))->toEqual('222');
    expect($contents->get('prepayid'))->toEqual('yansongda');
});

it('正常', function () {
    $plugin = new AppInvokePlugin();
    $rocket = (new Rocket())->setDestination(new Collection(['prepay_id' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->all())->toHaveKey('sign');
    expect($contents->all())->toHaveKey('timestamp');
    expect($contents->all())->toHaveKey('noncestr');
    expect($contents->get('appid'))->toEqual('yansongda');
    expect($contents->get('package'))->toEqual('Sign=WXPay');
    expect($contents->get('partnerid'))->toEqual('1600314069');
    expect($contents->get('prepayid'))->toEqual('yansongda');
});

it('服务商参数', function () {
    $plugin = new AppInvokePlugin();
    $rocket = (new Rocket())
        ->setParams(['_config' => 'service_provider4'])
        ->setDestination(new Collection(['prepay_id' => 'yansongda']))
        ->setPayload(['_invoke_appid' => '111', '_invoke_partnerid' => '222']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->all())->toHaveKey('sign');
    expect($contents->all())->toHaveKey('timestamp');
    expect($contents->all())->toHaveKey('noncestr');
    expect($contents->get('appid'))->toEqual('111');
    expect($contents->get('package'))->toEqual('Sign=WXPay');
    expect($contents->get('partnerid'))->toEqual('222');
    expect($contents->get('prepayid'))->toEqual('yansongda');
});

it('服务商', function () {
    $plugin = new AppInvokePlugin();
    $rocket = (new Rocket())
        ->setParams(['_config' => 'service_provider4'])
        ->setDestination(new Collection(['prepay_id' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->all())->toHaveKey('sign');
    expect($contents->all())->toHaveKey('timestamp');
    expect($contents->all())->toHaveKey('noncestr');
    expect($contents->get('appid'))->toEqual('wx55955316af4ef16');
    expect($contents->get('package'))->toEqual('Sign=WXPay');
    expect($contents->get('partnerid'))->toEqual('1600314069');
    expect($contents->get('prepayid'))->toEqual('yansongda');
});

