<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\Virtual\PayPlugin;
use Yansongda\Pay\Shortcut\Wechat\VirtualShortcut;
use Yansongda\Pay\Tests\TestCase;

class VirtualShortcutTest extends TestCase
{
    protected VirtualShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new VirtualShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            NoHttpRequestDirection::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
