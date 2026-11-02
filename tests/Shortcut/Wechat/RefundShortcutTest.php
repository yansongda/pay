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
use Yansongda\Pay\Plugin\Wechat\V3\Pay\App\RefundPlugin as AppRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\RefundPlugin as CombineRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\RefundPlugin as H5RefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\RefundPlugin as JsapiRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\RefundPlugin as MiniRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\RefundPlugin as NativeRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\RefundShortcut;

it('default', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        JsapiRefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('app', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins(['_action' => 'app']))->toEqual([
        StartPlugin::class,
        AppRefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('combine', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins(['_action' => 'combine']))->toEqual([
        StartPlugin::class,
        CombineRefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('h5', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins(['_action' => 'h5']))->toEqual([
        StartPlugin::class,
        H5RefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('jsapi', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins(['_action' => 'jsapi']))->toEqual([
        StartPlugin::class,
        JsapiRefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('mini', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins(['_action' => 'mini']))->toEqual([
        StartPlugin::class,
        MiniRefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('native', function () {
    $plugin = new RefundShortcut();
    expect($plugin->getPlugins(['_action' => 'native']))->toEqual([
        StartPlugin::class,
        NativeRefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('foo', function () {
    $plugin = new RefundShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $plugin->getPlugins(['_action' => 'foo']);
});

