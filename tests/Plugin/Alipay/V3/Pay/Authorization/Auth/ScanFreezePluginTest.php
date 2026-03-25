<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay\Authorization\Auth;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth\ScanFreezePlugin;
use Yansongda\Pay\Tests\TestCase;

class ScanFreezePluginTest extends TestCase
{
    protected ScanFreezePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ScanFreezePlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/fund/auth/order/voucher/create', $result->getPayload()->get('_url'));
        self::assertSame('PREAUTH_PAY', $result->getPayload()->get('_body')['product_code']);
    }
}
