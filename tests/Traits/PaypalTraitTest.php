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
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\PaypalTrait;
use Yansongda\Supports\Collection;

class PaypalTraitStub
{
    use PaypalTrait;
}

class PaypalTraitTest extends TestCase
{
    public function testGetPaypalUrl(): void
    {
        self::assertEquals('https://yansongda.cn', PaypalTraitStub::getPaypalUrl([], new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertEquals('https://api-m.paypal.com/v2/checkout/orders', PaypalTraitStub::getPaypalUrl([], new Collection(['_url' => 'v2/checkout/orders'])));
        self::assertEquals('https://api-m.sandbox.paypal.com/v2/checkout/orders', PaypalTraitStub::getPaypalUrl(['mode' => Pay::MODE_SANDBOX], new Collection(['_url' => 'v2/checkout/orders'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_PAYPAL_URL_MISSING);
        PaypalTraitStub::getPaypalUrl([], new Collection([]));
    }

    public function testGetPaypalAccessTokenCached(): void
    {
        Pay::get(ConfigInterface::class)->set('paypal.default._access_token', 'cached_token_123');
        Pay::get(ConfigInterface::class)->set('paypal.default._access_token_expiry', time() + 3600);

        $token = PaypalTraitStub::getPaypalAccessToken([]);

        self::assertEquals('cached_token_123', $token);
    }

    public function testGetPaypalAccessTokenMissingConfig(): void
    {
        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_PAYPAL_INVALID);

        PaypalTraitStub::getPaypalAccessToken(['_config' => 'empty_paypal']);
    }

    public function testGetPaypalAccessTokenFresh(): void
    {
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

        self::assertEquals('new_paypal_token_456', $token);
        self::assertEquals('new_paypal_token_456', Pay::get(ConfigInterface::class)->get('paypal.default._access_token'));
        self::assertNotEmpty(Pay::get(ConfigInterface::class)->get('paypal.default._access_token_expiry'));
    }

    public function testVerifyPaypalWebhookSignMissingWebhookId(): void
    {
        Pay::get(ConfigInterface::class)->set('paypal.default.webhook_id', '');

        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/paypal/notify', [
            'PAYPAL-TRANSMISSION-ID' => 'test-id',
            'PAYPAL-TRANSMISSION-SIG' => 'test-sig',
        ], '{}');

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_PAYPAL_INVALID);

        PaypalTraitStub::verifyPaypalWebhookSign($request, []);
    }

    public function testVerifyPaypalWebhookSignEmptySignature(): void
    {
        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/paypal/notify', [], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        PaypalTraitStub::verifyPaypalWebhookSign($request, []);
    }

    public function testVerifyPaypalWebhookSignSuccess(): void
    {
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
        self::assertTrue(true);
    }

    public function testVerifyPaypalWebhookSignFailure(): void
    {
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

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        PaypalTraitStub::verifyPaypalWebhookSign($request, []);
    }
}