<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\WebPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new WebPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/v1/checkout/sessions');
    expect($payload->get('mode'))->toEqual('payment');
});

it('配置默认 URL', function () {
    $plugin = new WebPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('success_url'))->toEqual('https://pay.yansongda.cn/stripe/success');
    expect($payload->get('cancel_url'))->toEqual('https://pay.yansongda.cn/stripe/cancel');
});

it('自定义 URL', function () {
    $plugin = new WebPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection([
        'success_url' => 'https://example.com/success',
        'cancel_url' => 'https://example.com/cancel',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('success_url'))->toEqual('https://example.com/success');
    expect($payload->get('cancel_url'))->toEqual('https://example.com/cancel');
});

it('自定义模式', function () {
    $plugin = new WebPlugin();
    $rocket = new Rocket();
    $rocket->setParams([])->setPayload(new Collection(['mode' => 'subscription']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('mode'))->toEqual('subscription');
});

it('缺少 success_url 抛出异常', function () {
    $plugin = new WebPlugin();

    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'no_success_url'])->setPayload(new Collection([]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

