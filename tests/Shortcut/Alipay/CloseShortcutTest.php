<?php

namespace Yansongda\Pay\Tests\Shortcut\Alipay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\ClosePlugin as AgreementClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\App\ClosePlugin as AppClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\ClosePlugin as AuthorizationClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\ClosePlugin as WapClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\ClosePlugin as MiniClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\ClosePlugin as PosClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\ClosePlugin as ScanClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\ClosePlugin as WebClosePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\CloseShortcut;
use Yansongda\Pay\Tests\TestCase;

class CloseShortcutTest extends TestCase
{
    protected CloseShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new CloseShortcut();
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
            WebClosePlugin::class,
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
            AgreementClosePlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
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
            AppClosePlugin::class,
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
            AuthorizationClosePlugin::class,
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
            MiniClosePlugin::class,
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
            PosClosePlugin::class,
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
            ScanClosePlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }

    public function testH5()
    {
        $result = $this->shortcut->getPlugins(['_action' => 'h5']);

        self::assertEquals([
            StartPlugin::class,
            WapClosePlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
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
            WebClosePlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $result);
    }
}
