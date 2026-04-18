<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Airwallex;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\CancelPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Airwallex\CancelShortcut;
use Yansongda\Pay\Tests\TestCase;

class CancelShortcutTest extends TestCase
{
    public function testNormal()
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            CancelPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], (new CancelShortcut())->getPlugins([]));
    }
}
