<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Douyin;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Pay\QueryCpsPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Pay\QueryPlugin as TradeQueryPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Refund\QueryRefundPlugin as TradeQueryRefundPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\ResponsePlugin;
use Yansongda\Pay\Shortcut\Douyin\TradeQueryShortcut;
use Yansongda\Pay\Tests\TestCase;

class TradeQueryShortcutTest extends TestCase
{
    protected TradeQueryShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new TradeQueryShortcut();
    }

    public function testDefault(): void
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            TradeQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }

    public function testOrder(): void
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            TradeQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'order']));
    }

    public function testCps(): void
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            QueryCpsPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'cps']));
    }

    public function testRefund(): void
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            TradeQueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'refund']));
    }

    public function testInvalidAction(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        $this->shortcut->getPlugins(['_action' => 'invalid_action']);
    }
}
