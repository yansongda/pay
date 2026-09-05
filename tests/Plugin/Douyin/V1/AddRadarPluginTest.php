<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Douyin\V1\AddRadarPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddRadarPluginTest extends TestCase
{
    protected AddRadarPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddRadarPlugin();
    }

    public function testNormalWithoutAccessToken(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);
        $rocket->setPayload(new Collection([
            '_url' => '/api/trade/v2/create/order',
            'out_order_no' => '20240101123456',
            'total_amount' => 1,
            'subject' => '测试商品',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('https://open-sandbox.douyin.com/api/trade/v2/create/order', (string) $radar->getUri());
        self::assertEquals('application/json', $radar->getHeaderLine('Content-Type'));
        self::assertEquals('yansongda/pay-v3', $radar->getHeaderLine('User-Agent'));
        self::assertFalse($radar->hasHeader('access-token'));
        self::assertEquals(json_encode([
            'out_order_no' => '20240101123456',
            'total_amount' => 1,
            'subject' => '测试商品',
        ], JSON_UNESCAPED_UNICODE), (string) $radar->getBody());
    }

    public function testAccessTokenHeaderAndBodyPriority(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);
        $rocket->setPayload(new Collection([
            '_url' => '/api/trade/v2/refund/create/refund',
            '_access_token' => 'clt.abc123',
            '_body' => '{"out_order_no":"20240101123456","refund_amount":1}',
            'ignored_field' => 'not_in_body',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('clt.abc123', $radar->getHeaderLine('access-token'));
        // `_body` 优先，业务字段不再重复打包
        self::assertEquals('{"out_order_no":"20240101123456","refund_amount":1}', (string) $radar->getBody());
    }

    public function testEmptyBodyWhenPayloadOnlyUnderscoreKeys(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);
        $rocket->setPayload(new Collection(['_url' => '/api/trade/v2/query/order']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('https://open-sandbox.douyin.com/api/trade/v2/query/order', (string) $radar->getUri());
        self::assertFalse($radar->hasHeader('access-token'));
        self::assertEquals('', (string) $radar->getBody());
    }
}
