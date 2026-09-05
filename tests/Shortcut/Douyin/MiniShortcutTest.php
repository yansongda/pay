<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Douyin;

use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\SignPlugin;
use Yansongda\Pay\Shortcut\Douyin\MiniShortcut;
use Yansongda\Pay\Tests\TestCase;

class MiniShortcutTest extends TestCase
{
    protected MiniShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new MiniShortcut();
    }

    public function testDefault()
    {
        self::assertSame([
            StartPlugin::class,
            SignPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
