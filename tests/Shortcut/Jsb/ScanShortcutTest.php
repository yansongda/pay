<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Jsb\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Jsb\AddRadarPlugin;
use Yansongda\Pay\Plugin\Jsb\Pay\Scan\PayPlugin;
use Yansongda\Pay\Plugin\Jsb\ResponsePlugin;
use Yansongda\Pay\Plugin\Jsb\StartPlugin;
use Yansongda\Pay\Plugin\Jsb\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Jsb\ScanShortcut;

it('默认', function () {
    $plugin = new ScanShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        PayPlugin::class,
        AddPayloadSignPlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

