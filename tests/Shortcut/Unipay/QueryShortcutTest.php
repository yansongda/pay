<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Unipay;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadBodyPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Pay\QrCode\QueryPlugin as QrCodeQueryPlugin;
use Yansongda\Pay\Plugin\Unipay\Pay\Web\QueryPlugin as OnlineGatewayQueryPlugin;
use Yansongda\Pay\Plugin\Unipay\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Unipay\QueryShortcut;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{
    protected QueryShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            OnlineGatewayQueryPlugin::class,
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
            QrCodeQueryPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'qr_code']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);
        self::expectExceptionMessage('Query action [fooPlugins] not supported');

        $this->plugin->getPlugins(['_action' => 'foo']);
    }
}
