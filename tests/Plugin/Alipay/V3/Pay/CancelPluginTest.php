<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\CancelPlugin;
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
        $result = $this->plugin->assembly((new Rocket())->setParams(['out_trade_no' => 'test123']), fn ($rocket) => $rocket);

        self::assertEquals('alipay.trade.cancel', $result->getPayload()->get('method'));
        self::assertEquals('test123', $result->getPayload()->get('biz_content')['out_trade_no']);
    }
}
