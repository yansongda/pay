<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayConfirmPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Airwallex\IntentShortcut;

it('正常', function () {
    expect((new IntentShortcut())->getPlugins([]))->toEqual([
        StartPlugin::class,
        ObtainAccessTokenPlugin::class,
        PayPlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
        PayConfirmPlugin::class,
    ]);
});

