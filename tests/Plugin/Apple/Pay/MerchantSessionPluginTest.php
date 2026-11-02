<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\MerchantSessionPlugin;

it('正常', function () {
    $plugin = new MerchantSessionPlugin();
    $url = 'https://apple-pay-gateway.apple.com/paymentservices/startSession';

    $rocket = (new Rocket())->setParams([
        'validation_url' => $url,
        'initiative_context' => 'https://shop.yansongda.cn',
    ]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual($url);
    expect($payload->get('_no_jwt'))->toBeTrue();

    $body = json_decode((string) $payload->get('_body'), true);
    expect($body)->toEqual([
        'merchantIdentifier' => 'merchant.com.yansongda.pay',
        'displayName' => 'merchant.com.yansongda.pay',
        'initiative' => 'web',
        'initiativeContext' => 'https://shop.yansongda.cn',
    ]);
});

it('参数中的 display_name 与 initiative_context', function () {
    $plugin = new MerchantSessionPlugin();
    $rocket = (new Rocket())->setParams([
        'validation_url' => 'https://cn-apple-pay-gateway.apple.com/paymentservices/startSession',
        'display_name' => 'Yansongda Store',
        'initiative_context' => 'https://shop.yansongda.cn',
    ]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $body = json_decode((string) $result->getPayload()->get('_body'), true);
    expect($body['merchantIdentifier'])->toEqual('merchant.com.yansongda.pay');
    expect($body['displayName'])->toEqual('Yansongda Store');
    expect($body['initiative'])->toEqual('web');
    expect($body['initiativeContext'])->toEqual('https://shop.yansongda.cn');
});

it('域名大小写不敏感', function () {
    $plugin = new MerchantSessionPlugin();
    $url = 'https://Apple-Pay-Gateway.APPLE.COM/paymentservices/startSession';

    $rocket = (new Rocket())->setParams([
        'validation_url' => $url,
        'initiative_context' => 'https://shop.yansongda.cn',
    ]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_url'))->toEqual($url);
});

it('缺少 initiative context 抛出异常', function () {
    $plugin = new MerchantSessionPlugin();

    $rocket = (new Rocket())->setParams(['validation_url' => 'https://apple-pay-gateway.apple.com/paymentservices/startSession']);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少 validation_url 抛出异常', function () {
    $plugin = new MerchantSessionPlugin();

    $rocket = (new Rocket())->setParams([]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('http 协议抛出异常', function () {
    $plugin = new MerchantSessionPlugin();

    $rocket = (new Rocket())->setParams(['validation_url' => 'http://apple-pay-gateway.apple.com/paymentservices/startSession']);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_APPLE_URL_MISSING);

it('非 Apple 域名抛出异常', function () {
    $plugin = new MerchantSessionPlugin();

    $rocket = (new Rocket())->setParams(['validation_url' => 'https://evil.com/paymentservices/startSession']);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_APPLE_URL_MISSING);

