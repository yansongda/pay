<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V2\FormatPayloadBizContentPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\Pay\App\PayPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin;
use Yansongda\Pay\Shortcut\Alipay\AppShortcut;

it('正常', function () {
    $shortcut = new AppShortcut();
    $result = $shortcut->getPlugins([]);

    expect($result)->toEqual([
        StartPlugin::class,
        PayPlugin::class,
        FormatPayloadBizContentPlugin::class,
        AddPayloadSignaturePlugin::class,
        ResponseInvokeStringPlugin::class,
        ParserPlugin::class,
    ]);
});

