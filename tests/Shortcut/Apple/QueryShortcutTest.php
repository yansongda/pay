<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Apple;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\QueryHistoryPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\QuerySubscriptionsPlugin;
use Yansongda\Pay\Plugin\Apple\ResponsePlugin;
use Yansongda\Pay\Shortcut\Apple\QueryShortcut;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{
    protected QueryShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new QueryShortcut();
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

    public function testTransactionAction()
    {
        self::assertEquals([
            StartPlugin::class,
            QueryPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'transaction']));
    }

    public function testHistoryAction()
    {
        self::assertEquals([
            StartPlugin::class,
            QueryHistoryPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'history']));
    }

    public function testSubscriptionsAction()
    {
        self::assertEquals([
            StartPlugin::class,
            QuerySubscriptionsPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins(['_action' => 'subscriptions']));
    }

    public function testUnknownAction()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);
        self::expectExceptionMessage('不支持的 _action [foo]');

        $this->shortcut->getPlugins(['_action' => 'foo']);
    }
}
