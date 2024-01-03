<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V2\Papay\Direct;

use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\MiniOnlyContractPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class MiniOnlyContractPluginTest extends TestCase
{
    protected MiniOnlyContractPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new MiniOnlyContractPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "out_trade_no" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals(NoHttpRequestDirection::class, $result->getDirection());
        self::assertEquals('111', $payload->get('out_trade_no'));
        self::assertEquals('wx55955316af4ef13', $payload->get('appid'));
        self::assertEquals('1600314069', $payload->get('mch_id'));
        self::assertNotEmpty($payload->get('timestamp'));
        self::assertArrayHasKey('notify_url', $payload->all());

        $destination = $result->getDestination();

        self::assertEquals('111', $destination->get('out_trade_no'));
        self::assertEquals('wx55955316af4ef13', $destination->get('appid'));
        self::assertEquals('1600314069', $destination->get('mch_id'));
        self::assertNotEmpty($destination->get('timestamp'));
        self::assertArrayHasKey('notify_url', $destination->all());
    }
}
