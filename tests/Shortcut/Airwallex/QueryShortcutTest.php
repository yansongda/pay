<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Airwallex;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Airwallex\QueryShortcut;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{
    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        (new QueryShortcut())->getPlugins(['_action' => 'foo']);
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            QueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], (new QueryShortcut())->getPlugins([]));
    }

    public function testRefund()
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            QueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], (new QueryShortcut())->getPlugins(['_action' => 'refund']));
    }
}
