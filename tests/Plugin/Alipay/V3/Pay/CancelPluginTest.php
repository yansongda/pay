<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\CancelPlugin;
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

    public function testNormal(): void
    {
        $params = ['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026'];
        $rocket = (new Rocket())->setParams($params)->setPayload(new Collection($params));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/alipay/trade/cancel', $result->getPayload()->get('_url'));

        // 业务字段原样透传（官方 AlipayTradeCancelModel 无 notify_url 字段，不注入）
        self::assertEquals('yansongda-2026', $result->getPayload()->get('out_trade_no'));
        self::assertNull($result->getPayload()->get('notify_url'));
    }
}
