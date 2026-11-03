<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund\QueryPlugin as TransferQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\QueryPlugin as AgreementQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\App\QueryPlugin as AppQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\App\QueryRefundPlugin as AppQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\QueryPlugin as AuthorizationQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\QueryRefundPlugin as AuthorizationQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Face\QueryPlugin as FaceQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\QueryPlugin as WapQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\QueryRefundPlugin as WapQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\QueryPlugin as MiniQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\QueryRefundPlugin as MiniQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\QueryPlugin as PosQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\QueryRefundPlugin as PosQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\QueryPlugin as ScanQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\QueryRefundPlugin as ScanQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\QueryPlugin as WebQueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\QueryRefundPlugin as WebQueryRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\QueryShortcut;

describe('非法动作', function () {
    it('动作非法', function () {
        $shortcut = new QueryShortcut();

        $shortcut->getPlugins(['_action' => 'foo']);
    })->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);
});

describe('query', function () {
    it('默认', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins([]);

        expect($result)->toEqual([
            StartPlugin::class,
            WebQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('协议', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'agreement']);

        expect($result)->toEqual([
            StartPlugin::class,
            AgreementQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('APP', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'app']);

        expect($result)->toEqual([
            StartPlugin::class,
            AppQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('授权', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'authorization']);

        expect($result)->toEqual([
            StartPlugin::class,
            AuthorizationQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('人脸', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'face']);

        expect($result)->toEqual([
            StartPlugin::class,
            FaceQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('小程序', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'mini']);

        expect($result)->toEqual([
            StartPlugin::class,
            MiniQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('POS', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'pos']);

        expect($result)->toEqual([
            StartPlugin::class,
            PosQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('扫码', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'scan']);

        expect($result)->toEqual([
            StartPlugin::class,
            ScanQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('H5', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'h5']);

        expect($result)->toEqual([
            StartPlugin::class,
            WapQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('网页', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'web']);

        expect($result)->toEqual([
            StartPlugin::class,
            WebQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('转账', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'transfer']);

        expect($result)->toEqual([
            StartPlugin::class,
            TransferQueryPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });
});

describe('queryRefund', function () {
    it('默认', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'refund']);

        expect($result)->toEqual([
            StartPlugin::class,
            WebQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('APP', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'refund_app']);

        expect($result)->toEqual([
            StartPlugin::class,
            AppQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('授权', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'refund_authorization']);

        expect($result)->toEqual([
            StartPlugin::class,
            AuthorizationQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('小程序', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'refund_mini']);

        expect($result)->toEqual([
            StartPlugin::class,
            MiniQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('POS', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'refund_pos']);

        expect($result)->toEqual([
            StartPlugin::class,
            PosQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('扫码', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'refund_scan']);

        expect($result)->toEqual([
            StartPlugin::class,
            ScanQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('H5', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'refund_h5']);

        expect($result)->toEqual([
            StartPlugin::class,
            WapQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });

    it('网页', function () {
        $shortcut = new QueryShortcut();
        $result = $shortcut->getPlugins(['_action' => 'refund_web']);

        expect($result)->toEqual([
            StartPlugin::class,
            WebQueryRefundPlugin::class,
            FormatPayloadBizContentPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ]);
    });
});