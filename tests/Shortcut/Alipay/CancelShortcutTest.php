<?php

namespace Yansongda\Pay\Tests\Shortcut\Alipay;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Agreement\CancelPlugin as AgreementCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Authorization\CancelPlugin as AuthorizationCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Mini\CancelPlugin as MiniCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Pos\CancelPlugin as PosCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Scan\CancelPlugin as ScanCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Shortcut\Alipay\CancelShortcut;
use Yansongda\Pay\Tests\TestCase;

class CancelShortcutTest extends TestCase
{
    protected CancelShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new CancelShortcut();
    }

    public function testFooParam()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

        $this->shortcut->getPlugins(['_action' => 'foo']);
    }

    public function testDefault()
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            PosCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testAgreement()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'agreement']);

        self::assertEquals([
            StartPlugin::class,
            AgreementCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testAuthorization()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'authorization']);

        self::assertEquals([
            StartPlugin::class,
            AuthorizationCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testMini()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'mini']);

        self::assertEquals([
            StartPlugin::class,
            MiniCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testPos()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'pos']);

        self::assertEquals([
            StartPlugin::class,
            PosCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

public function testScan()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'scan']);

        self::assertEquals([
            StartPlugin::class,
            ScanCancelPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
