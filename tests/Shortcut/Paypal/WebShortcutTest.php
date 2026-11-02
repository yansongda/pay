<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Paypal\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\CapturePlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;
use Yansongda\Pay\Shortcut\Paypal\WebShortcut;

it('foo', function () {
    $shortcut = new WebShortcut();

    $shortcut->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

it('默认', function () {
    $shortcut = new WebShortcut();
    expect($shortcut->getPlugins([]))->toEqual([
        StartPlugin::class,
        ObtainAccessTokenPlugin::class,
        PayPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('支付', function () {
    $shortcut = new WebShortcut();
    expect($shortcut->getPlugins(['_action' => 'pay']))->toEqual([
        StartPlugin::class,
        ObtainAccessTokenPlugin::class,
        PayPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('捕获', function () {
    $shortcut = new WebShortcut();
    expect($shortcut->getPlugins(['_action' => 'capture']))->toEqual([
        StartPlugin::class,
        ObtainAccessTokenPlugin::class,
        CapturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

