<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\RefundPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['capture_id' => 'CAP_123'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toContain('CAP_123');
    expect($payload->get('_url'))->toContain('refund');

    // capture_id 不应残留在 payload 中，否则会被打进最终请求 body
    expect($payload->get('capture_id'))->toBeNull();
});

it('携带金额', function () {
    $plugin = new RefundPlugin();
    $amount = ['currency_code' => 'USD', 'value' => '5.00'];

    $rocket = new Rocket();
    $rocket->setParams(['capture_id' => 'CAP_456'])->setPayload(new Collection(['amount' => $amount]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('amount'))->toEqual($amount);

    // 仅保留白名单内的业务字段
    expect($payload->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v2/payments/captures/CAP_456/refund',
        'amount' => $amount,
    ]);
});

it('缺少 capture ID', function () {
    $plugin = new RefundPlugin();

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

