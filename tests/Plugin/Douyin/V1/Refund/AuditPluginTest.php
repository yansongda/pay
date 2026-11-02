<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\AuditPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new AuditPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'refund_id' => '7016089888760043861',
        'refund_audit_status' => 1,
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toBe('POST');
    expect($payload->get('_url'))->toBe('/api/trade_basic/v1/developer/refund_audit_callback/');
    // 透传字段保留
    expect($payload->get('refund_id'))->toBe('7016089888760043861');
    expect($payload->get('refund_audit_status'))->toBe(1);
    // 无注入键：业务接口均无 app_id/notify_url 字段，不得注入
    expect(array_keys($payload->all()))->toBe(['refund_id', 'refund_audit_status', '_method', '_url']);
});

it('拒绝消息透传', function () {
    $plugin = new AuditPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'refund_id' => '7016089888760043861',
        'refund_audit_status' => 2,
        'deny_message' => '不支持退款',
    ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    // deny_message 仅透传，不做必填强校验（官方为服务端校验）
    expect($payload->get('deny_message'))->toBe('不支持退款');
    expect($payload->get('refund_audit_status'))->toBe(2);
});

it('空 payload', function () {
    $plugin = new AuditPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, '参数异常: 抖音退款审核，缺少必要的业务参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

