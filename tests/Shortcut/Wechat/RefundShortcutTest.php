<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Wechat;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Wechat\AddPayloadBodyPlugin;
use Yansongda\Pay\Plugin\Wechat\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\App\RefundPlugin as AppRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Combine\RefundPlugin as CombineRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\H5\RefundPlugin as H5RefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Jsapi\RefundPlugin as JsapiRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Mini\RefundPlugin as MiniRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\Pay\Native\RefundPlugin as NativeRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\RefundShortcut;
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
            JsapiRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins([]));
    }

    public function testApp()
    {
        self::assertEquals([
            StartPlugin::class,
            AppRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'app']));
    }

    public function testCombine()
    {
        self::assertEquals([
            StartPlugin::class,
            CombineRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'combine']));
    }

    public function testH5()
    {
        self::assertEquals([
            StartPlugin::class,
            H5RefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'h5']));
    }

    public function testJsapi()
    {
        self::assertEquals([
            StartPlugin::class,
            JsapiRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'jsapi']));
    }

    public function testMini()
    {
        self::assertEquals([
            StartPlugin::class,
            MiniRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'mini']));
    }

    public function testNative()
    {
        self::assertEquals([
            StartPlugin::class,
            NativeRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $this->plugin->getPlugins(['_action' => 'native']));
    }


    public function testFoo()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);
        self::expectExceptionMessage('Refund action [fooPlugins] not supported');

        $this->plugin->getPlugins(['_action' => 'foo']);
    }
}
