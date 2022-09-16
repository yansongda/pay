<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Plugin\Unipay\HtmlResponsePlugin;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\WapPayPlugin;
use Yansongda\Pay\Plugin\Unipay\Shortcut\WapShortcut;
use Yansongda\Pay\Tests\TestCase;

class WapShortcutTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new WapShortcut();
    }

    public function test()
    {
        self::assertEquals([
            WapPayPlugin::class,
            HtmlResponsePlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
