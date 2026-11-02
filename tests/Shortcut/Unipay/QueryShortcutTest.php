<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\QueryPlugin as QrCodeQueryPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\QueryPlugin as OnlineGatewayQueryPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\AddPayloadSignaturePlugin as QraAddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\Pos\QueryPlugin as QraPosQueryPlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\Pos\QueryRefundPlugin as QraPosQueryRefundPlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\StartPlugin as QraStartPlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\VerifySignaturePlugin as QraVerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Unipay\QueryShortcut;

it('默认', function () {
    $plugin = new QueryShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        OnlineGatewayQueryPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('二维码', function () {
    $plugin = new QueryShortcut();
    expect($plugin->getPlugins(['_action' => 'qr_code']))->toEqual([
        StartPlugin::class,
        QrCodeQueryPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('QRA POS', function () {
    $plugin = new QueryShortcut();
    expect($plugin->getPlugins(['_action' => 'qra_pos']))->toEqual([
        QraStartPlugin::class,
        QraPosQueryPlugin::class,
        QraAddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        QraVerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('QRA POS 退款', function () {
    $plugin = new QueryShortcut();
    expect($plugin->getPlugins(['_action' => 'qra_pos_refund']))->toEqual([
        QraStartPlugin::class,
        QraPosQueryRefundPlugin::class,
        QraAddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        QraVerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('foo', function () {
    $plugin = new QueryShortcut();

    $plugin->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

