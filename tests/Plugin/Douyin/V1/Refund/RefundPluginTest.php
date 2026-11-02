<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\RefundPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'order_id' => '7016089888760043860',
        'out_refund_no' => '202408040747147327',
        'refund_reason' => [['code' => 1, 'text' => '测试退款']],
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toBe('POST');
    expect($payload->get('_url'))->toBe('/api/trade_basic/v1/developer/refund_create/');
    // payload 无 notify_url 时，缺省注入 config 的 notify_url
    expect($payload->get('notify_url'))->toBe('https://yansongda.cn/douyin/notify');
    // 业务字段透传保留
    expect($payload->get('order_id'))->toBe('7016089888760043860');
    expect($payload->get('out_refund_no'))->toBe('202408040747147327');
    expect($payload->get('refund_reason'))->toBe([['code' => 1, 'text' => '测试退款']]);
    // 业务接口均无 app_id 字段，不得注入
    expect($payload->all())->not->toHaveKey('app_id');
});

it('显式 notify_url', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'order_id' => '7016089888760043860',
        'out_refund_no' => '202408040747147327',
        'refund_reason' => [['code' => 1, 'text' => '测试退款']],
        'notify_url' => 'https://explicit.yansongda.cn/douyin/notify',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    // 显式传入 notify_url 优先，不被 config 的 notify_url 覆盖
    expect($payload->get('notify_url'))->toBe('https://explicit.yansongda.cn/douyin/notify');
});

it('空 payload', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, '参数异常: 抖音创建退款，缺少必要的业务参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('仅保留过滤后的参数', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        '_foo' => 'bar',
        'notify_url' => null,
    ]));

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, '参数异常: 抖音创建退款，缺少必要的业务参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

