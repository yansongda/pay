<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay\Pos;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Pos\PayPlugin;
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
        $result = $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);

        self::assertSame('/v3/alipay/trade/pay', $result->getPayload()->get('_url'));
    }
}
