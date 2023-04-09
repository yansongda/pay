<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Shortcut;

use Yansongda\Pay\Plugin\Wechat\Papay\OnlyContractPlugin;
use Yansongda\Pay\Plugin\Wechat\PreparePlugin;
use Yansongda\Pay\Plugin\Wechat\Shortcut\PapayContractShortcut;
use Yansongda\Pay\Tests\TestCase;

class PapayContractShortcutTest extends TestCase
{
    protected PapayContractShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PapayContractShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            PreparePlugin::class,
            OnlyContractPlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
