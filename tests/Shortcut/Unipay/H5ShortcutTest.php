<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Unipay;

use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\HtmlResponsePlugin;
use Yansongda\Pay\Plugin\Unipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\WapPayPlugin;
use Yansongda\Pay\Plugin\Unipay\PreparePlugin;
use Yansongda\Pay\Plugin\Unipay\RadarSignPlugin;
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
            PreparePlugin::class,
            WapPayPlugin::class,
            HtmlResponsePlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
