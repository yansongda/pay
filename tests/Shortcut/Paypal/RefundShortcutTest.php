<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Paypal;

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\RefundPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;
use Yansongda\Pay\Shortcut\Paypal\RefundShortcut;
use Yansongda\Pay\Tests\TestCase;

class RefundShortcutTest extends TestCase
{
    protected RefundShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new RefundShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            ObtainAccessTokenPlugin::class,
            RefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }
}
