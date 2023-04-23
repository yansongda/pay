<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\Papay\ApplyPlugin;
use Yansongda\Pay\Plugin\Wechat\Papay\ContractOrderPlugin;
use Yansongda\Pay\Plugin\Wechat\Papay\OnlyContractPlugin;
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

    public function testDefaultMini()
    {
        self::assertEquals([
            PreparePlugin::class,
            ContractOrderPlugin::class,
            RadarSignPlugin::class,
            \Yansongda\Pay\Plugin\Wechat\Pay\Mini\InvokePrepayV2Plugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'mini']));
    }

    public function testDefaultApp()
    {
        self::assertEquals([
            PreparePlugin::class,
            ContractOrderPlugin::class,
            RadarSignPlugin::class,
            \Yansongda\Pay\Plugin\Wechat\Pay\App\InvokePrepayV2Plugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'app']));
    }

    public function testContract()
    {
        self::assertEquals([
            PreparePlugin::class,
            OnlyContractPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'contract']));
    }

    public function testApply()
    {
        self::assertEquals([
            PreparePlugin::class,
            ApplyPlugin::class,
            RadarSignPlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'apply']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_MULTI_ACTION_ERROR);
        self::expectExceptionMessage('Papay action [fooPlugins] not supported');

        $this->plugin->getPlugins(['_action' => 'foo']);
    }
}
