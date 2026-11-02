<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\ClosePlugin as CombineClosePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\ClosePlugin as JsapiClosePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\CloseShortcut;

it('default', function () {
    $plugin = new CloseShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        JsapiClosePlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('combine', function () {
    $plugin = new CloseShortcut();
    expect($plugin->getPlugins(['_action' => 'combine']))->toEqual([
        StartPlugin::class,
        CombineClosePlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('combine params', function () {
    $plugin = new CloseShortcut();
    expect($plugin->getPlugins(['combine_out_trade_no' => '123abc']))->toEqual([
        StartPlugin::class,
        CombineClosePlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);

    expect($plugin->getPlugins(['sub_orders' => '123abc']))->toEqual([
        StartPlugin::class,
        CombineClosePlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('virtual', function () {
    $plugin = new CloseShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $plugin->getPlugins(['_action' => 'virtual']);
});

it('foo', function () {
    $plugin = new CloseShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $plugin->getPlugins(['_action' => 'foo']);
});

