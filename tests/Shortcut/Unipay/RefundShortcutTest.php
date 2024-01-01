<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Unipay;

use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadBodyPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\OnlineGateway\RefundPlugin as OnlineGatewayRefundPlugin;
use Yansongda\Pay\Plugin\Unipay\QrCode\RefundPlugin as QrCodeRefundPlugin;
use Yansongda\Pay\Plugin\Unipay\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Unipay\RefundShortcut;
use Yansongda\Pay\Tests\TestCase;

class RefundShortcutTest extends TestCase
{
    protected RefundShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            OnlineGatewayRefundPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testQrCode()
    {
        self::assertEquals([
            StartPlugin::class,
            QrCodeRefundPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'qr_code']));
    }

    public function testFoo()
    {
        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionMessage('Refund action [fooPlugins] not supported');

        $this->plugin->getPlugins(['_action' => 'foo']);
    }
}
