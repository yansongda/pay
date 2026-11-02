<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Pos\CancelPlugin;
use Yansongda\Supports\Collection;

it('缺少 out', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'aaa' => 'aaa',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 付款码支付撤销订单，参数缺少 `out_trade_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_trade_no' => '111',
        'aaa' => 'aaa',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v3/pay/transactions/out-trade-no/111/reverse');
    expect($payload->get('appid'))->toEqual('wx55955316af4ef13');
    expect($payload->get('mchid'))->toEqual('1600314069');
});

it('服务商', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_trade_no' => '111',
        'aaa' => 'aaa',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_service_url'))->toEqual('/v3/pay/partner/transactions/out-trade-no/111/reverse');
    expect($payload->get('sp_appid'))->toEqual('wx55955316af4ef13');
    expect($payload->get('sp_mchid'))->toEqual('1600314069');
    expect($payload->get('sub_mchid'))->toEqual('1600314070');
});

it('服务商参数', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'sub_mchid' => '1222',
        'out_trade_no' => '111',
        'aaa' => 'aaa',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_service_url'))->toEqual('/v3/pay/partner/transactions/out-trade-no/111/reverse');
    expect($payload->get('sp_appid'))->toEqual('wx55955316af4ef13');
    expect($payload->get('sp_mchid'))->toEqual('1600314069');
    expect($payload->get('sub_mchid'))->toEqual('1222');
});

