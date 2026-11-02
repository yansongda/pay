<?php

declare(strict_types=1);

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

it('foo 参数', function () {
    $shortcut = new CloseShortcut();

    $shortcut->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

it('默认', function () {
    $shortcut = new CloseShortcut();
    $result = $shortcut->getPlugins([]);

    expect($result)->toEqual([
        StartPlugin::class,
        WebClosePlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('协议', function () {
    $shortcut = new CloseShortcut();
    $result = $shortcut->getPlugins(['_action' => 'agreement']);

    expect($result)->toEqual([
        StartPlugin::class,
        AgreementClosePlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('APP', function () {
    $shortcut = new CloseShortcut();
    $result = $shortcut->getPlugins(['_action' => 'app']);

    expect($result)->toEqual([
        StartPlugin::class,
        AppClosePlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('授权', function () {
    $shortcut = new CloseShortcut();
    $result = $shortcut->getPlugins(['_action' => 'authorization']);

    expect($result)->toEqual([
        StartPlugin::class,
        AuthorizationClosePlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('小程序', function () {
    $shortcut = new CloseShortcut();
    $result = $shortcut->getPlugins(['_action' => 'mini']);

    expect($result)->toEqual([
        StartPlugin::class,
        MiniClosePlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('POS', function () {
    $shortcut = new CloseShortcut();
    $result = $shortcut->getPlugins(['_action' => 'pos']);

    expect($result)->toEqual([
        StartPlugin::class,
        PosClosePlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('扫码', function () {
    $shortcut = new CloseShortcut();
    $result = $shortcut->getPlugins(['_action' => 'scan']);

    expect($result)->toEqual([
        StartPlugin::class,
        ScanClosePlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('H5', function () {
    $shortcut = new CloseShortcut();
    $result = $shortcut->getPlugins(['_action' => 'h5']);

    expect($result)->toEqual([
        StartPlugin::class,
        WapClosePlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('网页', function () {
    $shortcut = new CloseShortcut();
    $result = $shortcut->getPlugins(['_action' => 'web']);

    expect($result)->toEqual([
        StartPlugin::class,
        WebClosePlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

