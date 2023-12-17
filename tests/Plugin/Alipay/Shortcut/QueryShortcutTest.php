<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Alipay\Fund\TransCommonQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\LaunchPlugin;
use Yansongda\Pay\Plugin\Alipay\PreparePlugin;
use Yansongda\Pay\Plugin\Alipay\RadarSignPlugin;
use Yansongda\Pay\Plugin\Alipay\Shortcut\QueryShortcut;
use Yansongda\Pay\Plugin\Alipay\Trade\FastRefundQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Trade\QueryPlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{
    protected QueryShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            PreparePlugin::class,
            QueryPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testRefund()
    {
        self::assertEquals([
            PreparePlugin::class,
            FastRefundQueryPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'refund']));
    }

    public function testTransfer()
    {
        self::assertEquals([
            PreparePlugin::class,
            TransCommonQueryPlugin::class,
            RadarSignPlugin::class,
            LaunchPlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'transfer']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_MULTI_ACTION_ERROR);
        self::expectExceptionMessage('Query action [fooPlugins] not supported');

        $this->plugin->getPlugins(['_action' => 'foo']);
    }
}
