<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin;
use Yansongda\Pay\Tests\TestCase;

class RefundPluginTest extends TestCase
{
    protected RefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams(['out_trade_no' => 'test123']), fn ($rocket) => $rocket);

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/alipay/trade/refund', $result->getPayload()->get('_url'));
        self::assertEquals('test123', $result->getPayload()->get('_body')['out_trade_no']);
    }
}
