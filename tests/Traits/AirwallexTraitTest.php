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
use Yansongda\Pay\Traits\AirwallexTrait;
use Yansongda\Supports\Collection;

class AirwallexTraitStub
{
use AirwallexTrait;
}

it('get airwallex request id', function () {
    expect(AirwallexTraitStub::getAirwallexRequestId())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
});

it('get airwallex url', function () {
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

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_AIRWALLEX_URL_MISSING);
    AirwallexTraitStub::getAirwallexUrl($config, new Collection([]));
});

it('get airwallex access token from cache', function () {
    $config = Pay::get(ConfigInterface::class)->get('airwallex.default');
    $config->setAccessToken('cached_airwallex_token');
    $config->setAccessTokenExpiry(time() + 3600);

    expect(AirwallexTraitStub::getAirwallexAccessToken([]))->toBe('cached_airwallex_token');
});

it('get airwallex access token missing credentials', function () {
    $config = Pay::get(ConfigInterface::class)->get('airwallex.default');
    $config->setClientId('');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_AIRWALLEX_INVALID);

    AirwallexTraitStub::getAirwallexAccessToken([]);
});

it('verify airwallex webhook sign empty signature', function () {
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [], '{}');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);

    AirwallexTraitStub::verifyAirwallexWebhookSign($request, []);
});

it('verify airwallex webhook sign', function () {
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

it('verify airwallex webhook sign expired timestamp', function () {
    $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
    $timestamp = (string) (((int) (microtime(true) * 1000)) - 301000);
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    AirwallexTraitStub::verifyAirwallexWebhookSign($request, []);
});

