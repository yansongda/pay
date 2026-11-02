<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryRefundPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryRefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['refund_id' => 're_test456'])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toContain('re_test456');
    expect($payload->get('_url'))->toContain('v1/refunds/');
});

it('缺少退款单号', function () {
    $plugin = new QueryRefundPlugin();

    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

