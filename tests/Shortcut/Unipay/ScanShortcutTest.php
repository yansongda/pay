<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanFeePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPreAuthPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPreOrderPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Unipay\ScanShortcut;

it('默认', function () {
    $plugin = new ScanShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        ScanPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('预授权', function () {
    $plugin = new ScanShortcut();
    expect($plugin->getPlugins(['_action' => 'pre_auth']))->toEqual([
        StartPlugin::class,
        ScanPreAuthPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('预下单', function () {
    $plugin = new ScanShortcut();
    expect($plugin->getPlugins(['_action' => 'pre_order']))->toEqual([
        StartPlugin::class,
        ScanPreOrderPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('手续费', function () {
    $plugin = new ScanShortcut();
    expect($plugin->getPlugins(['_action' => 'fee']))->toEqual([
        StartPlugin::class,
        ScanFeePlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);
});

it('foo', function () {
    $plugin = new ScanShortcut();

    $plugin->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

