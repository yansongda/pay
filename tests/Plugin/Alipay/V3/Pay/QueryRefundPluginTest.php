<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryRefundPlugin;
use Yansongda\Pay\Tests\TestCase;

class QueryRefundPluginTest extends TestCase
{
    protected QueryRefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryRefundPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([
            'out_trade_no' => 'test123',
            'out_request_no' => 'refund123',
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('/v3/alipay/trade/fastpay/refund/query', $result->getPayload()->get('_url'));
        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('test123', $result->getPayload()->get('out_trade_no'));
        self::assertEquals('refund123', $result->getPayload()->get('out_request_no'));
    }
}
