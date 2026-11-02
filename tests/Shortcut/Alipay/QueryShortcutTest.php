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

it('foo param', function () {
    $shortcut = new QueryShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $shortcut->getPlugins(['_action' => 'foo']);
});

it('default', function () {
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

it('agreement', function () {
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

it('app', function () {
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

it('authorization', function () {
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

it('face', function () {
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

it('mini', function () {
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

it('pos', function () {
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

it('scan', function () {
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

it('h5', function () {
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

it('web', function () {
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

it('transfer', function () {
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

it('refund', function () {
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

it('refund app', function () {
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

it('refund authorization', function () {
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

it('refund mini', function () {
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

it('refund pos', function () {
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

it('refund scan', function () {
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

it('refund h5', function () {
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

it('refund web', function () {
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

