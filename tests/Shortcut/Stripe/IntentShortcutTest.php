<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\PayPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Stripe\IntentShortcut;

it('获取插件列表', function () {
    $shortcut = new IntentShortcut();
    expect($shortcut->getPlugins([]))->toEqual([
        StartPlugin::class,
        PayPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

