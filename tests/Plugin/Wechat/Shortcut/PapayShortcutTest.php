<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\Papay\ContractOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayV2Plugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\RadarSignPlugin;
use Yansongda\Pay\Plugin\Wechat\Shortcut\PapayShortcut;
use Yansongda\Pay\Tests\TestCase;

class PapayShortcutTest extends TestCase
{
    protected PapayShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PapayShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            PreparePlugin::class,
            ContractOrderPlugin::class,
            RadarSignPlugin::class,
            InvokePrepayV2Plugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testMini()
    {
        self::assertEquals([
            PreparePlugin::class,
            ContractOrderPlugin::class,
            RadarSignPlugin::class,
            \Yansongda\Pay\Plugin\Wechat\Pay\Mini\InvokePrepayV2Plugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testApp()
    {
        self::assertEquals([
            PreparePlugin::class,
            ContractOrderPlugin::class,
            RadarSignPlugin::class,
            \Yansongda\Pay\Plugin\Wechat\Pay\App\InvokePrepayV2Plugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
