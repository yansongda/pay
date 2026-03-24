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
        $rocket = new Rocket();
        $rocket->setParams(['out_trade_no' => 'test123', 'refund_amount' => '0.01']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('/v3/alipay/trade/refund', $result->getPayload()->get('_url'));
        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('test123', $result->getPayload()->get('out_trade_no'));
    }
}
