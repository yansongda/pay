<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\InvokePlugin;
use Yansongda\Pay\Shortcut\Douyin\MiniShortcut;

it('默认', function () {
    $plugin = new MiniShortcut();
    expect($plugin->getPlugins([]))->toBe([
        StartPlugin::class,
        InvokePlugin::class,
    ]);
});

