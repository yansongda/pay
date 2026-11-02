<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Unipay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\PayPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Unipay\Open\StartPlugin;
use Yansongda\Pay\Shortcut\Unipay\WebShortcut;

it('构建插件管道', function () {
    $shortcut = new WebShortcut();

    expect($shortcut->getPlugins([]))->toEqual([
        StartPlugin::class,
        PayPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddPayloadBodyPlugin::class,
        AddRadarPlugin::class,
        ResponseHtmlPlugin::class,
        ParserPlugin::class,
    ]);
});
