<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Apple;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\RefundPlugin;
use Yansongda\Pay\Plugin\Apple\ResponsePlugin;
use Yansongda\Pay\Shortcut\Apple\RefundShortcut;
use Yansongda\Pay\Tests\TestCase;

class RefundShortcutTest extends TestCase
{
    protected RefundShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new RefundShortcut();
    }

    public function testGetPlugins()
    {
        self::assertEquals([
            StartPlugin::class,
            RefundPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $this->shortcut->getPlugins([]));
    }
}
