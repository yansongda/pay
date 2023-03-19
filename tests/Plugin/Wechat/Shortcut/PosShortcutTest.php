<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Plugin\Wechat\Pay\Pos\PayPlugin;
use Yansongda\Pay\Plugin\Wechat\Shortcut\PosShortcut;
use Yansongda\Pay\Tests\TestCase;

class PosShortcutTest extends TestCase
{
    protected PosShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PosShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            PayPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
