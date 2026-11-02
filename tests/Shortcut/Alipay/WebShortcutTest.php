<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Shortcut\Alipay\WebShortcut;

it('正常', function () {
    $shortcut = new WebShortcut();
    $result = $shortcut->getPlugins([]);

    expect($result)->toEqual([
        StartPlugin::class,
        PayPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        ResponseHtmlPlugin::class,
        ParserPlugin::class,
    ]);
});

