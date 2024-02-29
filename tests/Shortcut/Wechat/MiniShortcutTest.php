<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\InvokePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\PayPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\MiniShortcut;
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
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            InvokePlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
