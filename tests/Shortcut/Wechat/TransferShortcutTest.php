<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\AddPayloadBodyPlugin;
use Yansongda\Pay\Plugin\Wechat\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\Marketing\Transfer\CreatePlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\TransferShortcut;
use Yansongda\Pay\Tests\TestCase;

class TransferShortcutTest extends TestCase
{
    protected TransferShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TransferShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            CreatePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
