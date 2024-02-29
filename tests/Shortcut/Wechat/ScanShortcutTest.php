<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\PayPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\ScanShortcut;
use Yansongda\Pay\Tests\TestCase;

class ScanShortcutTest extends TestCase
{
    protected ScanShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ScanShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
