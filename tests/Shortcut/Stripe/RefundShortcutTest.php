<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\RefundPlugin;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
use Yansongda\Pay\Shortcut\Stripe\RefundShortcut;

it('获取插件列表', function () {
    $shortcut = new RefundShortcut();
    expect($shortcut->getPlugins([]))->toEqual([
        StartPlugin::class,
        RefundPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

