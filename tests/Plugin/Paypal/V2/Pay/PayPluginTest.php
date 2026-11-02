<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\PayPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([
        'purchase_units' => [['amount' => ['currency_code' => 'USD', 'value' => '10.00']]],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v2/checkout/orders');
    expect($payload->get('intent'))->toEqual('CAPTURE');
});

it('defaults from config', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    $context = $payload->get('application_context');

    expect($context['return_url'])->toEqual('https://pay.yansongda.cn/paypal/return');
    expect($context['cancel_url'])->toEqual('https://pay.yansongda.cn/paypal/cancel');
});

it('custom intent', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['intent' => 'AUTHORIZE']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('intent'))->toEqual('AUTHORIZE');
});

it('application context params not residual', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([
        'purchase_units' => [['amount' => ['currency_code' => 'USD', 'value' => '10.00']]],
        'return_url' => 'https://example.com/return',
        'cancel_url' => 'https://example.com/cancel',
        'brand_name' => 'My Shop',
        'landing_page' => 'billing',
        'user_action' => 'PAY_NOW',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    // 已重组进 application_context 的顶层参数应被剔除，避免残留在最终请求 body 中
    expect($payload->get('return_url'))->toBeNull();
    expect($payload->get('cancel_url'))->toBeNull();
    expect($payload->get('brand_name'))->toBeNull();
    expect($payload->get('landing_page'))->toBeNull();
    expect($payload->get('user_action'))->toBeNull();

    $context = $payload->get('application_context');
    expect($context['return_url'])->toEqual('https://example.com/return');
    expect($context['cancel_url'])->toEqual('https://example.com/cancel');
    expect($context['brand_name'])->toEqual('My Shop');
});

