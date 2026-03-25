<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin;
use Yansongda\Pay\Tests\TestCase;

class ClosePluginTest extends TestCase
{
    protected ClosePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ClosePlugin();
    }

    public function testNormal()
    {
        $result = $this->plugin->assembly((new Rocket())->setParams(['out_trade_no' => 'test123']), fn ($rocket) => $rocket);

        self::assertEquals('alipay.trade.close', $result->getPayload()->get('method'));
        self::assertEquals('test123', $result->getPayload()->get('biz_content')['out_trade_no']);
    }
}
