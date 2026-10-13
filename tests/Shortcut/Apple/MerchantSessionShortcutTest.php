<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Apple;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\MerchantSessionPlugin;
use Yansongda\Pay\Plugin\Apple\ResponsePlugin;
use Yansongda\Pay\Shortcut\Apple\MerchantSessionShortcut;
use Yansongda\Pay\Tests\TestCase;

class MerchantSessionShortcutTest extends TestCase
{
    protected MerchantSessionShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new MerchantSessionShortcut();
    }

    public function testGetPlugins()
    {
        self::assertEquals([
            StartPlugin::class,
            MerchantSessionPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }

    public function testRocketDirectInstallWithAddRadarPlugin(): void
    {
        $url = 'https://apple-pay-gateway.apple.com/paymentservices/startSession';

        $rocket = (new Rocket())->setParams([
            'validation_url' => $url,
            'display_name' => 'Yansongda Store',
            'initiative_context' => 'https://shop.yansongda.cn',
        ]);

        $result = (new MerchantSessionPlugin())->assembly(
            $rocket,
            fn ($rocket) => (new AddRadarPlugin())->assembly($rocket, fn ($rocket) => $rocket)
        );

        $request = $result->getRadar();

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals($url, (string) $request->getUri());
        self::assertFalse($request->hasHeader('Authorization'));
        self::assertEquals('application/json', $request->getHeaderLine('Content-Type'));

        $body = json_decode((string) $request->getBody(), true);
        self::assertEquals('merchant.com.yansongda.pay', $body['merchantIdentifier']);
        self::assertEquals('Yansongda Store', $body['displayName']);
        self::assertEquals('web', $body['initiative']);
        self::assertEquals('https://shop.yansongda.cn', $body['initiativeContext']);
    }
    public function testMerchantSessionEndToEndWithHttpMock(): void
    {
        $http = \Mockery::mock(\GuzzleHttp\Client::class);
        $captured = null;
        $http->shouldReceive('sendRequest')->once()->with(\Mockery::on(function ($request) use (&$captured) {
            $captured = $request;

            return true;
        }))->andReturn(new \GuzzleHttp\Psr7\Response(200, [], (string) json_encode([
            'merchantSessionIdentifier' => 'mock-session',
            'nonce' => 'mock-nonce',
            'epochTimestamp' => 1,
            'expiresAt' => 2,
            'merchantIdentifier' => 'merchant.com.yansongda.pay',
            'domainName' => 'example.com',
            'signature' => 'mock-signature',
        ])));

        \Yansongda\Pay\Pay::set(\Yansongda\Artful\Contract\HttpClientInterface::class, $http);

        $result = \Yansongda\Pay\Pay::apple()->merchantSession([
            'validation_url' => 'https://apple-pay-gateway-cert.apple.com/paymentSession',
            'display_name' => '示例商户',
            'initiative_context' => 'https://example.com',
        ]);

        self::assertSame('https://apple-pay-gateway-cert.apple.com/paymentSession', (string) $captured->getUri());
        self::assertSame('', $captured->getHeaderLine('Authorization'));
        self::assertSame('application/json', $captured->getHeaderLine('Content-Type'));

        $body = (array) json_decode((string) $captured->getBody(), true);
        self::assertSame('merchant.com.yansongda.pay', $body['merchantIdentifier']);
        self::assertSame('示例商户', $body['displayName']);
        self::assertSame('web', $body['initiative']);
        self::assertSame('https://example.com', $body['initiativeContext']);

        self::assertInstanceOf(\Yansongda\Supports\Collection::class, $result);
        self::assertSame('mock-session', $result->get('merchantSessionIdentifier'));

        \Mockery::close();
    }

}
