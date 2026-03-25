<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay\Mini;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Mini\PayPlugin;
use Yansongda\Pay\Tests\TestCase;

class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'out_trade_no' => 'test123',
            'total_amount' => '0.01',
            'subject' => 'test',
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('/v3/alipay/trade/create', $result->getPayload()->get('_url'));
        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('test123', $result->getPayload()->get('out_trade_no'));
    }
}
