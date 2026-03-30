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
        $result = $this->plugin->assembly((new Rocket())->setParams(['out_request_no' => 'test123']), fn ($rocket) => $rocket);

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/alipay/trade/fastpay/refund/query', $result->getPayload()->get('_url'));
        self::assertEquals('test123', $result->getPayload()->get('_body')['out_request_no']);
    }
}
