<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Shortcut\Wechat\RedpackShortcut;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Redpack\SendPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\VerifySignaturePlugin;

class RedpackShortcutTest extends TestCase
{
    protected RedpackShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RedpackShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            SendPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
