<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Unipay;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadBodyPlugin;
use Yansongda\Pay\Plugin\Unipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Pay\QrCode\ScanFeePlugin;
use Yansongda\Pay\Plugin\Unipay\Pay\QrCode\ScanPlugin;
use Yansongda\Pay\Plugin\Unipay\Pay\QrCode\ScanPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\Pay\QrCode\ScanPreOrderPlugin;
use Yansongda\Pay\Plugin\Unipay\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Unipay\ScanShortcut;
use Yansongda\Pay\Tests\TestCase;

class ScanShortcutTest extends TestCase
{
    protected ScanShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ScanShortcut();
    }

    public function testDefault()
    {
        self::assertEquals([
            StartPlugin::class,
            ScanPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testPreAuth()
    {
        self::assertEquals([
            StartPlugin::class,
            ScanPreAuthPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'pre_auth']));
    }

    public function testPreOrder()
    {
        self::assertEquals([
            StartPlugin::class,
            ScanPreOrderPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'pre_order']));
    }

    public function testFee()
    {
        self::assertEquals([
            StartPlugin::class,
            ScanFeePlugin::class,
            AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'fee']));
    }

    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);
        self::expectExceptionMessage('Scan action [fooPlugins] not supported');

        $this->plugin->getPlugins(['_action' => 'foo']);
    }
}
