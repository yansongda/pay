<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
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

    public function testSuccess()
    {
        $result = Pay::airwallex()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertStringContainsString('success', (string) $result->getBody());
    }
}
