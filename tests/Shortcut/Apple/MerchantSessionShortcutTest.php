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
}
