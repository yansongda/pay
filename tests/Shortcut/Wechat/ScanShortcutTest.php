<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\PayPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Wechat\ScanShortcut;

it('默认', function () {
    $plugin = new ScanShortcut();
    expect($plugin->getPlugins([]))->toEqual([
        StartPlugin::class,
        PayPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        VerifySignaturePlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

