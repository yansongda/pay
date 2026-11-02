<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Jsb\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Jsb\AddRadarPlugin;
use Yansongda\Pay\Plugin\Jsb\Pay\Scan\QueryPlugin;
use Yansongda\Pay\Plugin\Jsb\ResponsePlugin;
use Yansongda\Pay\Plugin\Jsb\StartPlugin;
use Yansongda\Pay\Plugin\Jsb\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Jsb\QueryShortcut;

it('默认', function () {
    $plugin = new QueryShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        QueryPlugin::class,
        AddPayloadSignPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

