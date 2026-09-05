<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Douyin;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryCpsPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Douyin\QueryShortcut;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{
    protected QueryShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryShortcut();
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        $this->plugin->getPlugins(['_action' => 'foo']);
    }

    public function testDefault()
    {
        self::assertSame([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            QueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testOrder()
    {
        self::assertSame([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            QueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'order']));
    }

    public function testCps()
    {
        self::assertSame([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            QueryCpsPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'cps']));
    }

    public function testRefund()
    {
        self::assertSame([
            StartPlugin::class,
            ObtainClientTokenPlugin::class,
            QueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'refund']));
    }
}
