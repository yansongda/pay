<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryPluginTest extends TestCase
{
    protected QueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPlugin();
    }

    public function testNormal(): void
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026'])
            ->setPayload(new Collection(['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/alipay/trade/query', $result->getPayload()->get('_url'));

        // 业务字段原样透传，插件不干预（对齐 V2 查询条件语义：out_trade_no/trade_no 二选一由调用方决定）
        self::assertEquals('yansongda-2026', $result->getPayload()->get('out_trade_no'));
        self::assertNull($result->getPayload()->get('notify_url'));
    }

    public function testNormalWithTradeNo(): void
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'alipay-v3', 'trade_no' => '2026090222001400001234567890'])
            ->setPayload(new Collection(['_config' => 'alipay-v3', 'trade_no' => '2026090222001400001234567890']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/v3/alipay/trade/query', $result->getPayload()->get('_url'));
        self::assertEquals('2026090222001400001234567890', $result->getPayload()->get('trade_no'));
    }
}
