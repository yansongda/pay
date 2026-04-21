<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Stripe;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Stripe\QueryShortcut;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{
    protected QueryShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new QueryShortcut();
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        $this->shortcut->getPlugins(['_action' => 'foo']);
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            QueryPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }

    public function testOrder()
    {
        self::assertEquals([
            StartPlugin::class,
            QueryPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'order']));
    }

    public function testRefund()
    {
        self::assertEquals([
            StartPlugin::class,
            QueryRefundPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'refund']));
    }
}
