<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Shortcut;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Alipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\AddSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\FormatBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\Fund\Transfer\QueryPlugin as TransferQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Agreement\QueryPlugin as AgreementQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\App\QueryPlugin as AppQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\App\QueryRefundPlugin as AppQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Authorization\QueryPlugin as AuthorizationQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Authorization\QueryRefundPlugin as AuthorizationQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Face\QueryPlugin as FaceQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Mini\QueryPlugin as MiniQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Mini\QueryRefundPlugin as MiniQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Pos\QueryPlugin as PosQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Pos\QueryRefundPlugin as PosQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Scan\QueryPlugin as ScanQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Scan\QueryRefundPlugin as ScanQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Wap\QueryPlugin as WapQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Wap\QueryRefundPlugin as WapQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Web\QueryPlugin as WebQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\Pay\Web\QueryRefundPlugin as WebQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\Shortcut\QueryShortcut;
use Yansongda\Pay\Plugin\Alipay\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{
    protected QueryShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new QueryShortcut();
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
            WebQueryPlugin::class,
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
            AgreementQueryPlugin::class,
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
            AppQueryPlugin::class,
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
            AuthorizationQueryPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testFace()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'face']);

        self::assertEquals([
            StartPlugin::class,
            FaceQueryPlugin::class,
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
            MiniQueryPlugin::class,
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
            PosQueryPlugin::class,
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
            ScanQueryPlugin::class,
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
            WapQueryPlugin::class,
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
            WebQueryPlugin::class,
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
            TransferQueryPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testRefund()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'refund']);

        self::assertEquals([
            StartPlugin::class,
            WebQueryRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testRefundApp()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'refund_app']);

        self::assertEquals([
            StartPlugin::class,
            AppQueryRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testRefundAuthorization()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'refund_authorization']);

        self::assertEquals([
            StartPlugin::class,
            AuthorizationQueryRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testRefundMini()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'refund_mini']);

        self::assertEquals([
            StartPlugin::class,
            MiniQueryRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testRefundPos()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'refund_pos']);

        self::assertEquals([
            StartPlugin::class,
            PosQueryRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testRefundScan()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'refund_scan']);

        self::assertEquals([
            StartPlugin::class,
            ScanQueryRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testRefundWap()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'refund_wap']);

        self::assertEquals([
            StartPlugin::class,
            WapQueryRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testRefundWeb()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'refund_web']);

        self::assertEquals([
            StartPlugin::class,
            WebQueryRefundPlugin::class,
            FormatBizContentPlugin::class,
            AddSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
