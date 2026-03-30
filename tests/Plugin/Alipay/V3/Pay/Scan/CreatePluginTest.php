<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay\Scan;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Scan\CreatePlugin;
use Yansongda\Pay\Tests\TestCase;

class CreatePluginTest extends TestCase
{
    protected CreatePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CreatePlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);

        self::assertSame('/v3/alipay/trade/create', $result->getPayload()->get('_url'));
    }
}
