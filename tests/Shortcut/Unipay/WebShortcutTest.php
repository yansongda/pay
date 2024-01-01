<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Unipay;

use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\PagePayPlugin;
use Yansongda\Pay\Plugin\Unipay\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Unipay\StartPlugin;
use Yansongda\Pay\Shortcut\Unipay\WebShortcut;
use Yansongda\Pay\Tests\TestCase;

class WebShortcutTest extends TestCase
{
    protected WebShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new WebShortcut();
    }

    public function test()
    {
        self::assertEquals([
            StartPlugin::class,
            PagePayPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponseHtmlPlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
