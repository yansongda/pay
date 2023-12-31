<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\AddPayloadBodyPlugin;
use Yansongda\Pay\Plugin\Wechat\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\H5\PayPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\H5Shortcut;
use Yansongda\Pay\Tests\TestCase;

class H5ShortcutTest extends TestCase
{
    protected H5Shortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new H5Shortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
