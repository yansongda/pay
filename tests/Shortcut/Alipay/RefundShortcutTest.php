<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\RefundPlugin as FundTransferRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\RefundPlugin as AgreementRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\App\RefundPlugin as AppRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\RefundPlugin as AuthorizationRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\RefundPlugin as WapRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\RefundPlugin as MiniRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\RefundPlugin as PosRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\RefundPlugin as ScanRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\RefundPlugin as WebRefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\RefundShortcut;

it('foo 参数', function () {
    $shortcut = new RefundShortcut();

    $shortcut->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

it('默认', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins([]);

    expect($result)->toEqual([
        StartPlugin::class,
        WebRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('协议', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins(['_action' => 'agreement']);

    expect($result)->toEqual([
        StartPlugin::class,
        AgreementRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('APP', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins(['_action' => 'app']);

    expect($result)->toEqual([
        StartPlugin::class,
        AppRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('授权', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins(['_action' => 'authorization']);

    expect($result)->toEqual([
        StartPlugin::class,
        AuthorizationRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('小程序', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins(['_action' => 'mini']);

    expect($result)->toEqual([
        StartPlugin::class,
        MiniRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('POS', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins(['_action' => 'pos']);

    expect($result)->toEqual([
        StartPlugin::class,
        PosRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('扫码', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins(['_action' => 'scan']);

    expect($result)->toEqual([
        StartPlugin::class,
        ScanRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('H5', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins(['_action' => 'h5']);

    expect($result)->toEqual([
        StartPlugin::class,
        WapRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('网页', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins(['_action' => 'web']);

    expect($result)->toEqual([
        StartPlugin::class,
        WebRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('转账', function () {
    $shortcut = new RefundShortcut();
    $result = $shortcut->getPlugins(['_action' => 'transfer']);

    expect($result)->toEqual([
        StartPlugin::class,
        FundTransferRefundPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

