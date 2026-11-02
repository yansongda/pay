<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Stripe\QueryShortcut;

it('foo', function () {
    $shortcut = new QueryShortcut();

    $shortcut->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

it('默认', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins([]))->toEqual([
        StartPlugin::class,
        QueryPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('订单', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins(['_action' => 'order']))->toEqual([
        StartPlugin::class,
        QueryPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('退款', function () {
    $shortcut = new QueryShortcut();
    expect($shortcut->getPlugins(['_action' => 'refund']))->toEqual([
        StartPlugin::class,
        QueryRefundPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

