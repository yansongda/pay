<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Douyin;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Refund\RefundPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ResponsePlugin;
use Yansongda\Pay\Shortcut\Douyin\TradeRefundShortcut;
use Yansongda\Pay\Tests\TestCase;

class TradeRefundShortcutTest extends TestCase
{
    protected TradeRefundShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new TradeRefundShortcut();
    }

    public function testDefault(): void
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            RefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }
}
