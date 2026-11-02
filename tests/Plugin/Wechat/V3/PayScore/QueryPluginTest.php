<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\QueryPlugin;
use Yansongda\Supports\Collection;

it('缺少查询 ID', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'service_id' => '12345678901234567890123456789012',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询支付分订单，`out_order_no` 与 `query_id` 不允许都填写或都不填写', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('按商户订单号查询', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/payscore/serviceorder?out_order_no=202409101234567890&service_id=12345678901234567890123456789012&appid=wx55955316af4ef13',
    ]);
});

it('按 query_id 查询', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'query_id' => '156439172635391389723423423',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/payscore/serviceorder?query_id=156439172635391389723423423&service_id=12345678901234567890123456789012&appid=wx55955316af4ef13',
    ]);
});

it('同时提供两种参数', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
        'query_id' => '156439172635391389723423423',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询支付分订单，`out_order_no` 与 `query_id` 不允许都填写或都不填写', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少服务商 ID', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少支付分服务ID -- [service_id]', Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);

