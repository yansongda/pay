<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay\Authorization\Auth;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth\CancelPlugin;
use Yansongda\Pay\Tests\TestCase;

class CancelPluginTest extends TestCase
{
    protected CancelPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CancelPlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertSame('POST', $result->getPayload()->get('_method'));
        self::assertSame('/v3/alipay/fund/auth/operation/cancel', $result->getPayload()->get('_url'));
    }
}
