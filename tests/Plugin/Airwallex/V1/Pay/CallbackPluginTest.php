<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\CallbackPlugin;

it('缺少请求对象抛出异常', function () {
    $plugin = new CallbackPlugin();

    $plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

it('非法 JSON body 抛出异常', function () {
    $plugin = new CallbackPlugin();
    $body = 'not-valid-json';
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $plugin->assembly((new Rocket())->setParams(['_request' => $request, '_params' => []]), fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_AIRWALLEX_BODY_INVALID);

it('正常回调', function () {
    $plugin = new CallbackPlugin();
    $body = json_encode([
        'id' => 'evt_test123',
        'name' => 'payment_intent.succeeded',
    ]);
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $result = $plugin->assembly((new Rocket())->setParams(['_request' => $request, '_params' => []]), fn ($rocket) => $rocket);

    expect($result->getPayload()->get('id'))->toEqual('evt_test123');
    expect($result->getDestination()->get('name'))->toEqual('payment_intent.succeeded');
});

it('回调使用租户配置', function () {
    $plugin = new CallbackPlugin();
    $body = json_encode([
        'id' => 'evt_secondary',
        'name' => 'payment_intent.succeeded',
    ]);
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_secondary_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $result = $plugin->assembly((new Rocket())->setParams(['_request' => $request, '_params' => ['_config' => 'secondary']]), fn ($rocket) => $rocket);

    expect($result->getPayload()->get('id'))->toEqual('evt_secondary');
    expect($result->getDestination()->get('name'))->toEqual('payment_intent.succeeded');
});

