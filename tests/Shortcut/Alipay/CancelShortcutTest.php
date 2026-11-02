<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\CancelPlugin as AgreementCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\CancelPlugin as AuthorizationCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\CancelPlugin as MiniCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\CancelPlugin as PosCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\CancelPlugin as ScanCancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\CancelShortcut;

it('foo param', function () {
    $shortcut = new CancelShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $shortcut->getPlugins(['_action' => 'foo']);
});

it('default', function () {
    $shortcut = new CancelShortcut();
    $result = $shortcut->getPlugins([]);

    expect($result)->toEqual([
        StartPlugin::class,
        PosCancelPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('agreement', function () {
    $shortcut = new CancelShortcut();
    $result = $shortcut->getPlugins(['_action' => 'agreement']);

    expect($result)->toEqual([
        StartPlugin::class,
        AgreementCancelPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('authorization', function () {
    $shortcut = new CancelShortcut();
    $result = $shortcut->getPlugins(['_action' => 'authorization']);

    expect($result)->toEqual([
        StartPlugin::class,
        AuthorizationCancelPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('mini', function () {
    $shortcut = new CancelShortcut();
    $result = $shortcut->getPlugins(['_action' => 'mini']);

    expect($result)->toEqual([
        StartPlugin::class,
        MiniCancelPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('pos', function () {
    $shortcut = new CancelShortcut();
    $result = $shortcut->getPlugins(['_action' => 'pos']);

    expect($result)->toEqual([
        StartPlugin::class,
        PosCancelPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('scan', function () {
    $shortcut = new CancelShortcut();
    $result = $shortcut->getPlugins(['_action' => 'scan']);

    expect($result)->toEqual([
        StartPlugin::class,
        ScanCancelPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

