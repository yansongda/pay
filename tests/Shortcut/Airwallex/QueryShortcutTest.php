<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryRefundPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Airwallex\QueryShortcut;

it('foo', function () {

    (new QueryShortcut())->getPlugins(['_action' => 'foo']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

it('默认', function () {
    expect((new QueryShortcut())->getPlugins([]))->toEqual([
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
    expect((new QueryShortcut())->getPlugins(['_action' => 'refund']))->toEqual([
        StartPlugin::class,
        ObtainAccessTokenPlugin::class,
        QueryRefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

