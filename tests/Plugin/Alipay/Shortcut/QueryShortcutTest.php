<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Alipay\Fund\TransCommonQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Shortcut\QueryShortcut;
use Yansongda\Pay\Plugin\Alipay\Trade\FastRefundQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Trade\QueryPlugin;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            QueryPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testRefund()
    {
        self::assertEquals([
            FastRefundQueryPlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'refund']));
    }

    public function testTransfer()
    {
        self::assertEquals([
            TransCommonQueryPlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'transfer']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_MULTI_TYPE_ERROR);
        self::expectExceptionMessage('Query type [fooPlugins] not supported');

        $this->plugin->getPlugins(['_type' => 'foo']);
    }
}
