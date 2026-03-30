<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay\Authorization\Pay;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Pay\SyncPlugin;
use Yansongda\Pay\Tests\TestCase;

class SyncPluginTest extends TestCase
{
    protected SyncPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new SyncPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/trade/orderinfo/sync', $result->getPayload()->get('_url'));
    }
}
