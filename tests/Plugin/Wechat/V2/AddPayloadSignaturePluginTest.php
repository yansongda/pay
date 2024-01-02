<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V2;

use Yansongda\Pay\Plugin\Wechat\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddPayloadSignaturePluginTest extends TestCase
{
    protected AddPayloadSignaturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddPayloadSignaturePlugin();
    }

    public function testGetSignatureContent()
    {
        $payload1 = new Collection([
            '_method' => 'POST',
            '_url' => 'yansongda',
            'a' => null,
            'out_trade_no' => 1626493236,
            'description' => 'yansongda 测试 - 1626493236',
        ]);
        $rocket1 = (new Rocket())->setPayload($payload1);
        $result1 = $this->plugin->assembly($rocket1, fn ($rocket) => $rocket);

        $payload2 = new Collection([
            'out_trade_no' => 1626493236,
            'description' => 'yansongda 测试 - 1626493236',
        ]);
        $rocket2 = (new Rocket())->setPayload($payload2);
        $result2 = $this->plugin->assembly($rocket2, fn ($rocket) => $rocket);

        self::assertEquals($result2->getPayload()->get('sign'), $result1->getPayload()->get('sign'));
    }
}
