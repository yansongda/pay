<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Airwallex;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayConfirmPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Airwallex\IntentShortcut;
use Yansongda\Pay\Tests\TestCase;

class IntentShortcutTest extends TestCase
{
    public function testNormal()
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            PayPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
            PayConfirmPlugin::class,
        ], (new IntentShortcut())->getPlugins([]));
    }
}
