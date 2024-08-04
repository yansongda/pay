<?php

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\AddRadarPlugin;
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

    public function testNormal()
    {
        $params = [];
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'api/apps/ecpay/v1/create_order',
            '_body' => '123',
        ]);

        $rocket = (new Rocket())->setParams($params)->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('yansongda/pay-v3', $radar->getHeaderLine('User-Agent'));
        self::assertEquals('application/json; charset=utf-8', $radar->getHeaderLine('Content-Type'));
        self::assertEquals('123', (string) $radar->getBody());
        self::assertEquals('POST', $radar->getMethod());
        self::assertStringContainsString('api/apps/ecpay/v1/create_order', (string) $radar->getUri());
    }
}
