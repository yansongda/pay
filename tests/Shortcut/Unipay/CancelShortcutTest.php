<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\CancelPlugin as QrCodeCancelPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\CancelPlugin as OnlineGatewayCancelPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\AddPayloadSignaturePlugin as QraAddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\Pos\CancelPlugin as QraPosCancelQueryPlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\StartPlugin as QraStartPlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\VerifySignaturePlugin as QraVerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Unipay\CancelShortcut;

it('默认', function () {
    $plugin = new CancelShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        OnlineGatewayCancelPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('二维码', function () {
    $plugin = new CancelShortcut();
    expect($plugin->getPlugins(['_action' => 'qr_code']))->toEqual([
        StartPlugin::class,
        QrCodeCancelPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('QRA POS', function () {
    $plugin = new CancelShortcut();
    expect($plugin->getPlugins(['_action' => 'qra_pos']))->toEqual([
        QraStartPlugin::class,
        QraPosCancelQueryPlugin::class,
        QraAddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        QraVerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('foo', function () {
    $plugin = new CancelShortcut();

    $plugin->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

