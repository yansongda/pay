<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\PaypalConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\PaypalTrait;
use Yansongda\Supports\Collection;

class PaypalTraitStub
{
use PaypalTrait;
}

uses(PaypalTraitTestHelpers::class);

it('get paypal url', function () {
    expect(PaypalTraitStub::getPaypalUrl(new PaypalConfig([
        'client_id' => 'paypal_client_id',
        'app_secret' => 'paypal_app_secret',
    ]), new Collection(['_url' => 'https://yansongda.cn'])))->toEqual('https://yansongda.cn');
    expect(PaypalTraitStub::getPaypalUrl(new PaypalConfig([
        'client_id' => 'paypal_client_id',
        'app_secret' => 'paypal_app_secret',
    ]), new Collection(['_url' => '/v2/checkout/orders'])))->toEqual('https://api-m.paypal.com/v2/checkout/orders');
    expect(PaypalTraitStub::getPaypalUrl(new PaypalConfig([
        'client_id' => 'paypal_client_id',
        'app_secret' => 'paypal_app_secret',
        'mode' => PayMode::SANDBOX,
    ]), new Collection(['_url' => '/v2/checkout/orders'])))->toEqual('https://api-m.sandbox.paypal.com/v2/checkout/orders');

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_PAYPAL_URL_MISSING);
    PaypalTraitStub::getPaypalUrl(new PaypalConfig([
        'client_id' => 'paypal_client_id',
        'app_secret' => 'paypal_app_secret',
    ]), new Collection([]));
});

it('get paypal access token cached', function () {
    $paypalConfig = $this->getPaypalConfig();

    $paypalConfig->setAccessToken('cached_token_123');
    $paypalConfig->setAccessTokenExpiry(time() + 3600);

    $token = PaypalTraitStub::getPaypalAccessToken([]);

    expect($token)->toEqual('cached_token_123');
});

it('get paypal access token cached with return rocket param', function () {
    $paypalConfig = $this->getPaypalConfig();

    $paypalConfig->setAccessToken('cached_token_123');
    $paypalConfig->setAccessTokenExpiry(time() + 3600);

    // `_return_rocket` 不应影响缓存命中
    $token = PaypalTraitStub::getPaypalAccessToken(['_return_rocket' => true]);

    expect($token)->toEqual('cached_token_123');
});

it('get paypal access token refreshed with return rocket param', function () {
    $tokenResponse = new Response(200, [], json_encode([
        'access_token' => 'fresh_paypal_token_789',
        'token_type' => 'Bearer',
        'expires_in' => 32400,
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->once()->andReturn($tokenResponse);

    Pay::set(HttpClientInterface::class, $http);

    // `_return_rocket` 被内部剔除，仍能正确解析 token 并写入缓存
    $token = PaypalTraitStub::getPaypalAccessToken(['_return_rocket' => true]);

    expect($token)->toEqual('fresh_paypal_token_789');

    $paypalConfig = Pay::get(ConfigInterface::class)->get('paypal.default');

    expect($paypalConfig)->toBeInstanceOf(PaypalConfig::class);
    expect($paypalConfig->getAccessToken())->toEqual('fresh_paypal_token_789');
    expect($paypalConfig->getAccessTokenExpiry())->not->toBeEmpty();
});

it('get paypal access token missing config', function () {
    $paypalConfig = $this->getPaypalConfig();
    $paypalConfig->setClientId('');
    $paypalConfig->setAppSecret('');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_PAYPAL_INVALID);

    PaypalTraitStub::getPaypalAccessToken([]);
});

it('get paypal access token fresh', function () {
    $response = new Response(
        200,
        [],
        json_encode([
            'access_token' => 'new_paypal_token_456',
            'token_type' => 'Bearer',
            'expires_in' => 32400,
        ])
    );

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);

    Pay::set(HttpClientInterface::class, $http);

    $token = PaypalTraitStub::getPaypalAccessToken([]);

    expect($token)->toEqual('new_paypal_token_456');

    $paypalConfig = Pay::get(ConfigInterface::class)->get('paypal.default');

    expect($paypalConfig)->toBeInstanceOf(PaypalConfig::class);
    expect($paypalConfig->getAccessToken())->toEqual('new_paypal_token_456');
    expect($paypalConfig->getAccessTokenExpiry())->not->toBeEmpty();
});

it('verify paypal webhook sign missing webhook id', function () {
    $paypalConfig = $this->getPaypalConfig();

    $paypalConfig->setWebhookId('');

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/paypal/notify', [
        'PAYPAL-TRANSMISSION-ID' => 'test-id',
        'PAYPAL-TRANSMISSION-SIG' => 'test-sig',
    ], '{}');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_PAYPAL_INVALID);

    PaypalTraitStub::verifyPaypalWebhookSign($request, []);
});

it('verify paypal webhook sign empty signature', function () {
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/paypal/notify', [], '{}');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);

    PaypalTraitStub::verifyPaypalWebhookSign($request, []);
});

it('verify paypal webhook sign missing headers', function () {
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/paypal/notify', [
        'PAYPAL-TRANSMISSION-ID' => 'test-id',
        'PAYPAL-TRANSMISSION-SIG' => 'test-sig',
    ], '{}');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);

    PaypalTraitStub::verifyPaypalWebhookSign($request, []);
});

it('verify paypal webhook sign success', function () {
    $tokenResponse = new Response(200, [], json_encode([
        'access_token' => 'verify_token_123',
        'token_type' => 'Bearer',
        'expires_in' => 32400,
    ]));
    $verifyResponse = new Response(200, [], json_encode([
        'verification_status' => 'SUCCESS',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $verifyResponse);

    Pay::set(HttpClientInterface::class, $http);

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/paypal/notify', [
        'PAYPAL-TRANSMISSION-ID' => 'test-id',
        'PAYPAL-TRANSMISSION-TIME' => '2024-01-01T00:00:00Z',
        'PAYPAL-TRANSMISSION-SIG' => 'test-sig',
        'PAYPAL-CERT-URL' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-123',
        'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
    ], json_encode(['event_type' => 'CHECKOUT.ORDER.APPROVED']));

    PaypalTraitStub::verifyPaypalWebhookSign($request, []);
    expect(true)->toBeTrue();
});

it('verify paypal webhook sign failure', function () {
    $tokenResponse = new Response(200, [], json_encode([
        'access_token' => 'verify_token_123',
        'token_type' => 'Bearer',
        'expires_in' => 32400,
    ]));
    $verifyResponse = new Response(200, [], json_encode([
        'verification_status' => 'FAILURE',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $verifyResponse);

    Pay::set(HttpClientInterface::class, $http);

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/paypal/notify', [
        'PAYPAL-TRANSMISSION-ID' => 'test-id',
        'PAYPAL-TRANSMISSION-TIME' => '2024-01-01T00:00:00Z',
        'PAYPAL-TRANSMISSION-SIG' => 'test-sig',
        'PAYPAL-CERT-URL' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-123',
        'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
    ], json_encode(['event_type' => 'CHECKOUT.ORDER.APPROVED']));

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    PaypalTraitStub::verifyPaypalWebhookSign($request, []);
});

trait PaypalTraitTestHelpers
{
    private function getPaypalConfig(): PaypalConfig
    {
        $config = PaypalTraitStub::getProviderConfig('paypal', []);
    
        self::assertInstanceOf(PaypalConfig::class, $config);
    
        return $config;
    }
}
