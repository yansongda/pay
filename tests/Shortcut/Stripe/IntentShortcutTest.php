<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Stripe;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Stripe\IntentShortcut;
use Yansongda\Pay\Tests\TestCase;

class IntentShortcutTest extends TestCase
{
    protected IntentShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new IntentShortcut();
    }

    public function testGetPlugins()
    {
        self::assertEquals([
            StartPlugin::class,
            PayPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }
}
