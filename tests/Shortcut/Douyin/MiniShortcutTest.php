<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Douyin;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\Mini\PayPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\ResponsePlugin;
use Yansongda\Pay\Shortcut\Douyin\MiniShortcut;
use Yansongda\Pay\Tests\TestCase;

class MiniShortcutTest extends TestCase
{
    protected MiniShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new MiniShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
