<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\QueryPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'refund_id' => '7016089888760043861',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toBe('POST');
    expect($payload->get('_url'))->toBe('/api/trade_basic/v1/developer/refund_query/');
    // 透传字段保留
    expect($payload->get('refund_id'))->toBe('7016089888760043861');
    // 无注入键：业务接口均无 app_id/notify_url 字段，不得注入
    expect(array_keys($payload->all()))->toBe(['refund_id', '_method', '_url']);
});

it('退款单号与订单号', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_refund_no' => '202408040747147327',
        'order_id' => '7016089888760043860',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    expect($payload->get('out_refund_no'))->toBe('202408040747147327');
    expect($payload->get('order_id'))->toBe('7016089888760043860');
    expect($payload->all())->not->toHaveKey('app_id');
    expect($payload->all())->not->toHaveKey('notify_url');
});

it('空 payload', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, '参数异常: 抖音查询退款，缺少必要的业务参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('仅保留过滤后的参数', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        '_foo' => 'bar',
        'refund_id' => null,
    ]));

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, '参数异常: 抖音查询退款，缺少必要的业务参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

