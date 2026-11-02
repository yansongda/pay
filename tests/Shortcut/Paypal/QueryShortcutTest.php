<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Paypal\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;
use Yansongda\Pay\Shortcut\Paypal\QueryShortcut;

it('foo', function () {
    $shortcut = new QueryShortcut();

    $shortcut->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

it('默认', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins([]))->toEqual([
        StartPlugin::class,
        ObtainAccessTokenPlugin::class,
        QueryPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('订单', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins(['_action' => 'order']))->toEqual([
        StartPlugin::class,
        ObtainAccessTokenPlugin::class,
        QueryPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('退款', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins(['_action' => 'refund']))->toEqual([
        StartPlugin::class,
        ObtainAccessTokenPlugin::class,
        QueryRefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

