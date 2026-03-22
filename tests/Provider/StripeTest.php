<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class StripeTest extends TestCase
{
    public function testShortcutNotFound()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(\Yansongda\Artful\Exception\Exception::PARAMS_SHORTCUT_INVALID);

        Pay::stripe()->foo();
    }

    public function testMergeCommonPlugins()
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [StartPlugin::class],
            $plugins,
            [AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class],
        ), Pay::stripe()->mergeCommonPlugins($plugins));
    }

    public function testClose()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_METHOD_NOT_SUPPORTED);

        Pay::stripe()->close([]);
    }

    public function testCallback()
    {
        $body = json_encode([
            'id' => 'evt_test123',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test123',
                    'status' => 'succeeded',
                ],
            ],
        ]);

        // localhost skips signature verification
        $request = new ServerRequest('POST', 'http://localhost', [], $body);

        $result = Pay::stripe()->callback($request);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('evt_test123', $result->get('id'));
        self::assertEquals('payment_intent.succeeded', $result->get('type'));
    }

    public function testCallbackWithArray()
    {
        $body = json_encode([
            'id' => 'evt_test456',
            'type' => 'charge.succeeded',
            'data' => [
                'object' => [
                    'id' => 'ch_test456',
                    'status' => 'succeeded',
                ],
            ],
        ]);

        $result = Pay::stripe()->callback([
            'headers' => ['Stripe-Signature' => 'test-sig'],
            'body' => $body,
        ]);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('evt_test456', $result->get('id'));
        self::assertEquals('charge.succeeded', $result->get('type'));
    }

    public function testSuccess()
    {
        $result = Pay::stripe()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertStringContainsString('success', (string) $result->getBody());
    }
}
