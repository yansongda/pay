<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\AirwallexConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Stubs\AirwallexTraitStub;

it('获取 Airwallex 请求 ID', function () {
    expect(AirwallexTraitStub::getAirwallexRequestId())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
});

it('获取 Airwallex URL', function () {
    $config = new AirwallexConfig([
        'client_id' => 'airwallex_client_id',
        'api_key' => 'airwallex_api_key',
    ]);
    $sandboxConfig = new AirwallexConfig([
        'client_id' => 'airwallex_client_id',
        'api_key' => 'airwallex_api_key',
        'mode' => PayMode::SANDBOX,
    ]);

    expect(AirwallexTraitStub::getAirwallexUrl($config, new Collection(['_url' => 'https://yansongda.cn'])))->toBe('https://yansongda.cn');
    expect(AirwallexTraitStub::getAirwallexUrl($config, new Collection(['_url' => '/api/v1/authentication/login'])))->toBe('https://api.airwallex.com/api/v1/authentication/login');
    expect(AirwallexTraitStub::getAirwallexUrl($sandboxConfig, new Collection(['_url' => '/api/v1/authentication/login'])))->toBe('https://api-demo.airwallex.com/api/v1/authentication/login');

    AirwallexTraitStub::getAirwallexUrl($config, new Collection([]));
})->throws(InvalidParamsException::class, null, Exception::PARAMS_AIRWALLEX_URL_MISSING);

it('从缓存获取 Airwallex access token', function () {
    $config = Pay::get(ConfigInterface::class)->get('airwallex.default');
    $config->setAccessToken('cached_airwallex_token');
    $config->setAccessTokenExpiry(time() + 3600);

    expect(AirwallexTraitStub::getAirwallexAccessToken([]))->toBe('cached_airwallex_token');
});

it('Airwallex access token 缺少凭证', function () {
    $config = Pay::get(ConfigInterface::class)->get('airwallex.default');
    $config->setClientId('');

    AirwallexTraitStub::getAirwallexAccessToken([]);
})->throws(InvalidConfigException::class, null, Exception::CONFIG_AIRWALLEX_INVALID);

it('Airwallex webhook 验签：签名为空', function () {
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [], '{}');

    AirwallexTraitStub::verifyAirwallexWebhookSign($request, []);
})->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

it('Airwallex webhook 验签', function () {
    $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    AirwallexTraitStub::verifyAirwallexWebhookSign($request, []);

    expect(true)->toBeTrue();
});

it('Airwallex webhook 验签：时间戳过期', function () {
    $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
    $timestamp = (string) (((int) (microtime(true) * 1000)) - 301000);
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    AirwallexTraitStub::verifyAirwallexWebhookSign($request, []);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

