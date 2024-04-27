<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Pay\Pos;

use Yansongda\Pay\Plugin\Wechat\V3\Pay\Pos\CancelPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CancelPluginTest extends TestCase
{
    protected CancelPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CancelPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            "out_trade_no" => "111",
            'aaa' => 'aaa'
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEqualsCanonicalizing([
            '_method' => 'POST',
            '_url' => 'v3/pay/transactions/out-trade-no/111/reverse',
            'appid' => 'wx55955316af4ef13',
            'mchid' => '1600314069'
        ], $payload->all());
    }
}
