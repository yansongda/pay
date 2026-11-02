<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\PosPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\PosPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\VerifySignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\AddPayloadSignaturePlugin as QraAddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\Pos\PayPlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\StartPlugin as QraStartPlugin;
use Yansongda\Pay\Plugin\Unipay\Qra\VerifySignaturePlugin as QraVerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Unipay\PosShortcut;

it('默认', function () {
    $plugin = new PosShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        PosPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('预授权', function () {
    $plugin = new PosShortcut();
    expect($plugin->getPlugins(['_action' => 'pre_auth']))->toEqual([
        StartPlugin::class,
        PosPreAuthPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('QRA', function () {
    $plugin = new PosShortcut();
    expect($plugin->getPlugins(['_action' => 'qra']))->toEqual([
        QraStartPlugin::class,
        PayPlugin::class,
        QraAddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        QraVerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('foo', function () {
    $plugin = new PosShortcut();

    $plugin->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

