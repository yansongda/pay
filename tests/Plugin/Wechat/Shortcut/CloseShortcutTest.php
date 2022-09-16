<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Alipay\Fund\TransCommonQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Common\ClosePlugin;
use Yansongda\Pay\Plugin\Wechat\Shortcut\CloseShortcut;
use Yansongda\Pay\Tests\TestCase;

class CloseShortcutTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CloseShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            ClosePlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testCombine()
    {
        self::assertEquals([
            \Yansongda\Pay\Plugin\Wechat\Pay\Combine\ClosePlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'combine']));
    }

    public function testCombineParams()
    {
        self::assertEquals([
            \Yansongda\Pay\Plugin\Wechat\Pay\Combine\ClosePlugin::class,
        ], $this->plugin->getPlugins(['combine_out_trade_no' => '123abc']));

        self::assertEquals([
            \Yansongda\Pay\Plugin\Wechat\Pay\Combine\ClosePlugin::class,
        ], $this->plugin->getPlugins(['sub_orders' => '123abc']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_MULTI_TYPE_ERROR);
        self::expectExceptionMessage('Query type [fooPlugins] not supported');

        $this->plugin->getPlugins(['_type' => 'foo']);
    }
}
