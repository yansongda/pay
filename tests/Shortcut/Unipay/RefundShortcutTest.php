<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\RefundPlugin as QrCodeRefundPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\RefundPlugin as OnlineGatewayRefundPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\AddPayloadSignaturePlugin as QraAddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\Pos\RefundPlugin as QraPosRefundPlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\StartPlugin as QraStartPlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\VerifySignaturePlugin as QraVerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Unipay\RefundShortcut;

it('default', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        OnlineGatewayRefundPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('qr code', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins(['_action' => 'qr_code']))->toEqual([
        StartPlugin::class,
        QrCodeRefundPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('qra pos', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins(['_action' => 'qra_pos']))->toEqual([
        QraStartPlugin::class,
        QraPosRefundPlugin::class,
        QraAddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        QraVerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('foo', function () {
    $plugin = new RefundShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $plugin->getPlugins(['_action' => 'foo']);
});

