<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Plugin\Unipay\OnlineGateway\CancelPlugin;
use Yansongda\Pay\Plugin\Unipay\Shortcut\CancelShortcut;
use Yansongda\Pay\Tests\TestCase;

class CancelShortcutTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CancelShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            CancelPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testQrCode()
    {
        self::assertEquals([
            \Yansongda\Pay\Plugin\Unipay\QrCode\CancelPlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'qr_code']));
    }
}
