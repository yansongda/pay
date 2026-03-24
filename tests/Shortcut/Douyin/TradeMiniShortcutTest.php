<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Douyin;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ResponsePlugin;
use Yansongda\Pay\Shortcut\Douyin\TradeMiniShortcut;
use Yansongda\Pay\Tests\TestCase;

class TradeMiniShortcutTest extends TestCase
{
    protected TradeMiniShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new TradeMiniShortcut();
    }

    public function testDefault(): void
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }
}
