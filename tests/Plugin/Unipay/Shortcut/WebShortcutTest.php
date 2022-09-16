<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Plugin\Unipay\HtmlResponsePlugin;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\PagePayPlugin;
use Yansongda\Pay\Plugin\Unipay\Shortcut\WebShortcut;
use Yansongda\Pay\Tests\TestCase;

class WebShortcutTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new WebShortcut();
    }

    public function test()
    {
        self::assertEquals([
            PagePayPlugin::class,
            HtmlResponsePlugin::class,
        ], $this->plugin->getPlugins([]));
    }
}
