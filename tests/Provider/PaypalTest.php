<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Paypal\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PaypalTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::paypal()->foo();
    }

    public function testShortcutIncompatible()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::paypal()->foo();
    }

    public function testMergeCommonPlugins()
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [StartPlugin::class, ObtainAccessTokenPlugin::class],
            $plugins,
            [AddPayloadBodyPlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class],
        ), Pay::paypal()->mergeCommonPlugins($plugins));
    }

    public function testCancel()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(\Yansongda\Pay\Exception\Exception::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::paypal()->cancel([]);
    }

    public function testClose()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(\Yansongda\Pay\Exception\Exception::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::paypal()->close([]);
    }

    public function testCallback()
    {
        $body = json_encode([
            'event_type' => 'CHECKOUT.ORDER.APPROVED',
            'resource' => ['id' => 'ORDER_123', 'status' => 'APPROVED'],
        ]);

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'test_token_123',
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
        ], $body);

        $result = Pay::paypal()->callback($request);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('CHECKOUT.ORDER.APPROVED', $result->get('event_type'));
        self::assertEquals('ORDER_123', $result->get('resource.id'));
    }

    public function testCallbackWithArray()
    {
        $body = json_encode([
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['id' => 'CAPTURE_456', 'status' => 'COMPLETED'],
        ]);

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'test_token_456',
            'token_type' => 'Bearer',
            'expires_in' => 32400,
        ]));
        $verifyResponse = new Response(200, [], json_encode([
            'verification_status' => 'SUCCESS',
        ]));

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $verifyResponse);
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::paypal()->callback([
            'headers' => [
                'PAYPAL-TRANSMISSION-ID' => 'test-id',
                'PAYPAL-TRANSMISSION-TIME' => '2024-01-01T00:00:00Z',
                'PAYPAL-TRANSMISSION-SIG' => 'test-sig',
                'PAYPAL-CERT-URL' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-123',
                'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            ],
            'body' => $body,
        ]);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('PAYMENT.CAPTURE.COMPLETED', $result->get('event_type'));
        self::assertEquals('CAPTURE_456', $result->get('resource.id'));
    }

    public function testSuccess()
    {
        $result = Pay::paypal()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertStringContainsString('success', (string) $result->getBody());
    }
}
