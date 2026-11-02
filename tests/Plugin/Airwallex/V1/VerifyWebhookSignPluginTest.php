<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Airwallex\V1\VerifyWebhookSignPlugin;
use Yansongda\Pay\Tests\Support\Traits\AirwallexWebhookSignHelpers;

uses(AirwallexWebhookSignHelpers::class);

it('缺少 webhook 密钥抛出异常', function () {
    $request = new ServerRequest('POST', 'https://example.com', [], '{}');

    $this->verifyWebhookSign($request, ['_config' => 'no_webhook_secret']);
})->throws(InvalidConfigException::class, null, Exception::CONFIG_AIRWALLEX_INVALID);

it('缺少签名抛出异常', function () {
    $request = new ServerRequest('POST', 'https://example.com', [], '{}');

    $this->verifyWebhookSign($request, []);
})->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

it('签名错误抛出异常', function () {
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => '1710000000000',
        'x-signature' => 'wrong-signature',
    ], '{"id":"evt_test"}');

    $this->verifyWebhookSign($request, []);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('时间戳过期抛出异常', function () {
    $body = '{"id":"evt_test"}';
    $timestamp = (string) (((int) (microtime(true) * 1000)) - 301000);
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $this->verifyWebhookSign($request, []);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('签名有效通过', function () {
    $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $this->verifyWebhookSign($request, []);

    expect(true)->toBeTrue();
});

it('插件使用 destination 原始请求', function () {
    $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $result = (new VerifyWebhookSignPlugin())->assembly(
        (new Rocket())->setParams([])->setDestinationOrigin($request),
        fn ($rocket) => $rocket
    );

    expect($result)->toBeInstanceOf(Rocket::class);
});

it('插件使用租户配置', function () {
    $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_secondary_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $result = (new VerifyWebhookSignPlugin())->assembly(
        (new Rocket())->setParams(['_config' => 'secondary'])->setDestinationOrigin($request),
        fn ($rocket) => $rocket
    );

    expect($result)->toBeInstanceOf(Rocket::class);
});

it('插件缺少请求对象抛出异常', function () {

    (new VerifyWebhookSignPlugin())->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);
