<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\AddSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Fund\Transfer\RefundPlugin as FundTransferRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Agreement\RefundPlugin as AgreementRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\App\RefundPlugin as AppRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Authorization\RefundPlugin as AuthorizationRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Mini\RefundPlugin as MiniRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Pos\RefundPlugin as PosRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Scan\RefundPlugin as ScanRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Wap\RefundPlugin as WapRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Web\RefundPlugin as WebRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\Shortcut\RefundShortcut;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Tests\TestCase;

class RefundShortcutTest extends TestCase
{
    protected RefundShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new RefundShortcut();
    }

    public function testFooParam()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::SHORTCUT_MULTI_ACTION_ERROR);

        $this->shortcut->getPlugins(['_action' => 'foo']);
    }

    public function testDefault()
    {
        $result = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            WebRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            AgreementRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testApp()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'app']);

        self::assertEquals([
            StartPlugin::class,
            AppRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            AuthorizationRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            MiniRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            PosRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
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
            ScanRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testWap()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'wap']);

        self::assertEquals([
            StartPlugin::class,
            WapRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testWeb()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'web']);

        self::assertEquals([
            StartPlugin::class,
            WebRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testTransfer()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'transfer']);

        self::assertEquals([
            StartPlugin::class,
            FundTransferRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
