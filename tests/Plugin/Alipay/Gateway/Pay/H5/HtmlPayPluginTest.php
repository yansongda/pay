<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\Gateway\Pay\H5;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\Gateway\Pay\H5\HtmlPayPlugin;
use Yansongda\Pay\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class HtmlPayPluginTest extends TestCase
{
    protected HtmlPayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new HtmlPayPlugin();
    }

    public function testNormal(): void
    {
        $result = $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);

        self::assertEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.trade.wap.pay', $result->getPayload()->toJson());
    }
}
