<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;

use Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\CancelPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\CancelShortcut;
use Yansongda\Pay\Tests\TestCase;

class CancelShortcutTest extends TestCase
{
    protected CancelShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CancelShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            CancelPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testMchTransfer()
    {
        self::assertEquals([
            StartPlugin::class,
            CancelPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'mch_transfer']));
    }
}
