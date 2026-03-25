<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay\Authorization\Auth;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth\PosFreezePlugin;
use Yansongda\Pay\Tests\TestCase;

class PosFreezePluginTest extends TestCase
{
    protected PosFreezePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosFreezePlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/fund/auth/order/freeze', $result->getPayload()->get('_url'));
        self::assertSame('PREAUTH_PAY', $result->getPayload()->get('_body')['product_code']);
        self::assertSame('bar_code', $result->getPayload()->get('_body')['auth_code_type']);
    }
}
