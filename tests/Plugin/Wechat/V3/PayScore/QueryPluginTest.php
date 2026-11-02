<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\QueryPlugin;
use Yansongda\Supports\Collection;

it('missing query id', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'service_id' => '12345678901234567890123456789012',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 查询支付分订单，`out_order_no` 与 `query_id` 不允许都填写或都不填写');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('query by out order no', function () {
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

it('query by query id', function () {
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

it('both params', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
        'query_id' => '156439172635391389723423423',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 查询支付分订单，`out_order_no` 与 `query_id` 不允许都填写或都不填写');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('service id missing', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
    $this->expectExceptionMessage('参数异常: 缺少支付分服务ID -- [service_id]');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

