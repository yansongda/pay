<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\RefundPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Airwallex\RefundShortcut;

it('正常', function () {
    expect((new RefundShortcut())->getPlugins([]))->toEqual([
        StartPlugin::class,
        ObtainAccessTokenPlugin::class,
        RefundPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

