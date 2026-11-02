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

it('默认', function () {
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

it('二维码', function () {
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

it('QRA POS', function () {
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

    $plugin->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

