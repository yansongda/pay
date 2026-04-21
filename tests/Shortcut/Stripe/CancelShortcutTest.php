<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Stripe;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\CancelPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Stripe\CancelShortcut;
use Yansongda\Pay\Tests\TestCase;

class CancelShortcutTest extends TestCase
{
    protected CancelShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new CancelShortcut();
    }

    public function testGetPlugins()
    {
        self::assertEquals([
            StartPlugin::class,
            CancelPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }
}
