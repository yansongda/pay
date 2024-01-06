<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Unipay;

use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadBodyPlugin;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\H5\PayPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Shortcut\Unipay\H5Shortcut;
use Yansongda\Pay\Tests\TestCase;

class H5ShortcutTest extends TestCase
{
    protected H5Shortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new H5Shortcut();
    }

    public function test()
    {
        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
