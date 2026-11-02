<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\MerchantSessionPlugin;

it('normal', function () {
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

it('display name and initiative context from params', function () {
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

it('host case insensitive', function () {
    $plugin = new MerchantSessionPlugin();
    $url = 'https://Apple-Pay-Gateway.APPLE.COM/paymentservices/startSession';

    $rocket = (new Rocket())->setParams([
        'validation_url' => $url,
        'initiative_context' => 'https://shop.yansongda.cn',
    ]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_url'))->toEqual($url);
});

it('missing initiative context throws exception', function () {
    $plugin = new MerchantSessionPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $rocket = (new Rocket())->setParams(['validation_url' => 'https://apple-pay-gateway.apple.com/paymentservices/startSession']);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

it('missing validation url throws exception', function () {
    $plugin = new MerchantSessionPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $rocket = (new Rocket())->setParams([]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

it('http scheme throws exception', function () {
    $plugin = new MerchantSessionPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_APPLE_URL_MISSING);

    $rocket = (new Rocket())->setParams(['validation_url' => 'http://apple-pay-gateway.apple.com/paymentservices/startSession']);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

it('non apple host throws exception', function () {
    $plugin = new MerchantSessionPlugin();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_APPLE_URL_MISSING);

    $rocket = (new Rocket())->setParams(['validation_url' => 'https://evil.com/paymentservices/startSession']);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

