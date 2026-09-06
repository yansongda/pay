<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundPluginTest extends TestCase
{
    protected RefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundPlugin();
    }

    public function testNormal(): void
    {
        $params = [
            '_config' => 'alipay-v3',
            'out_trade_no' => 'yansongda-2026',
            'out_request_no' => 'yansongda-2026-refund',
            'refund_amount' => '3.00',
            'refund_reason' => '正常退款',
        ];
        $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/alipay/trade/refund', $result->getPayload()->get('_url'));

        // 业务字段原样透传（官方 AlipayTradeRefundModel 无 notify_url 字段，不注入）
        self::assertEquals('yansongda-2026', $result->getPayload()->get('out_trade_no'));
        self::assertEquals('yansongda-2026-refund', $result->getPayload()->get('out_request_no'));
        self::assertEquals('3.00', $result->getPayload()->get('refund_amount'));
        self::assertEquals('正常退款', $result->getPayload()->get('refund_reason'));
        self::assertNull($result->getPayload()->get('notify_url'));
    }
}
