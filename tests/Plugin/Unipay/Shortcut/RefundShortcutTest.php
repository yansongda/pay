<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Unipay\Shortcut;

use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\RefundPlugin;
use Yansongda\Pay\Plugin\Unipay\Shortcut\RefundShortcut;
use Yansongda\Pay\Tests\TestCase;

class RefundShortcutTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            RefundPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testQrCode()
    {
        self::assertEquals([
            \Yansongda\Pay\Plugin\Unipay\QrCode\RefundPlugin::class,
        ], $this->plugin->getPlugins(['_type' => 'qr_code']));
    }

    public function testInvalidType()
    {
        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionMessage('Refund type [InvalidTypePlugins] not supported');

        $this->plugin->getPlugins(['_type' => 'invalid_type']);
    }
}
