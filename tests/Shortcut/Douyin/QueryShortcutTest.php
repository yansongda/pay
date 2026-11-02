<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ObtainClientTokenPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryCpsPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\QueryPlugin as RefundQueryPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Douyin\QueryShortcut;

it('foo', function () {
    $plugin = new QueryShortcut();
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_SHORTCUT_ACTION_INVALID);

    $plugin->getPlugins(['_action' => 'foo']);
});

it('default', function () {
    $plugin = new QueryShortcut();
    expect($plugin->getPlugins([]))->toBe([
        StartPlugin::class,
        ObtainClientTokenPlugin::class,
        QueryPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('order', function () {
    $plugin = new QueryShortcut();
    expect($plugin->getPlugins(['_action' => 'order']))->toBe([
        StartPlugin::class,
        ObtainClientTokenPlugin::class,
        QueryPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('cps', function () {
    $plugin = new QueryShortcut();
    expect($plugin->getPlugins(['_action' => 'cps']))->toBe([
        StartPlugin::class,
        ObtainClientTokenPlugin::class,
        QueryCpsPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('refund', function () {
    $plugin = new QueryShortcut();
    expect($plugin->getPlugins(['_action' => 'refund']))->toBe([
        StartPlugin::class,
        ObtainClientTokenPlugin::class,
        RefundQueryPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

