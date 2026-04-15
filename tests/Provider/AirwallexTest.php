<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AirwallexTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(\Yansongda\Artful\Exception\Exception::PARAMS_SHORTCUT_INVALID);

        Pay::airwallex()->foo();
    }

    public function testMergeCommonPlugins()
    {
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [StartPlugin::class, ObtainAccessTokenPlugin::class],
            $plugins,
            [AddPayloadBodyPlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class],
        ), Pay::airwallex()->mergeCommonPlugins($plugins));
    }

    public function testClose()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::airwallex()->close([]);
    }

    public function testCallback()
    {
        $body = json_encode([
            'id' => 'evt_test123',
            'name' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'int_test123',
                    'status' => 'SUCCEEDED',
                ],
            ],
        ]);

        $timestamp = (string) round(microtime(true) * 1000);
        $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ], $body);

        $result = Pay::airwallex()->callback($request);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('evt_test123', $result->get('id'));
        self::assertEquals('payment_intent.succeeded', $result->get('name'));
    }

    public function testCallbackWithArray()
    {
        $body = json_encode([
            'id' => 'evt_test456',
            'name' => 'payment_intent.failed',
        ]);

        $timestamp = (string) round(microtime(true) * 1000);
        $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');

        $result = Pay::airwallex()->callback([
            'headers' => [
                'x-timestamp' => $timestamp,
                'x-signature' => $signature,
            ],
            'body' => $body,
        ]);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('evt_test456', $result->get('id'));
        self::assertEquals('payment_intent.failed', $result->get('name'));
    }

    public function testQuery()
    {
        $tokenResponse = new Response(201, [], json_encode([
            'token' => 'airwallex_query_token',
            'expires_at' => gmdate('Y-m-d\\TH:i:sO', time() + 1800),
        ]));
        $queryResponse = new Response(200, [], json_encode([
            'id' => 'int_query_123',
            'status' => 'SUCCEEDED',
        ]));

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $queryResponse);
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::airwallex()->query(['id' => 'int_query_123']);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('int_query_123', $result->get('id'));
    }

    public function testCancel()
    {
        $tokenResponse = new Response(201, [], json_encode([
            'token' => 'airwallex_cancel_token',
            'expires_at' => gmdate('Y-m-d\\TH:i:sO', time() + 1800),
        ]));
        $cancelResponse = new Response(200, [], json_encode([
            'id' => 'int_cancel_123',
            'status' => 'CANCELLED',
        ]));

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $cancelResponse);
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::airwallex()->cancel(['id' => 'int_cancel_123']);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('CANCELLED', $result->get('status'));
    }

    public function testRefund()
    {
        $tokenResponse = new Response(201, [], json_encode([
            'token' => 'airwallex_refund_token',
            'expires_at' => gmdate('Y-m-d\\TH:i:sO', time() + 1800),
        ]));
        $refundResponse = new Response(201, [], json_encode([
            'id' => 'ref_123',
            'status' => 'RECEIVED',
        ]));

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $refundResponse);
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::airwallex()->refund([
            'id' => 'int_refund_123',
            'amount' => 100,
        ]);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('ref_123', $result->get('id'));
    }

    public function testSuccess()
    {
        $result = Pay::airwallex()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertStringContainsString('success', (string) $result->getBody());
    }
}
