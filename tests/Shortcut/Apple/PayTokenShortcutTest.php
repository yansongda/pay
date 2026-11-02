<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Apple\Pay\PayTokenPlugin;
use Yansongda\Pay\Shortcut\Apple\PayTokenShortcut;
use Yansongda\Pay\Tests\Support\Apple\AppleTokenFactory;
use Yansongda\Supports\Collection;

it('获取插件列表', function () {
    $shortcut = new PayTokenShortcut();
    expect($shortcut->getPlugins([]))->toEqual([
        StartPlugin::class,
        PayTokenPlugin::class,
    ]);
});

it('pay token', function () {
    $token = AppleTokenFactory::makeToken(['applicationPrimaryAccountID' => '123456', 'totalAmount' => '100']);

    $result = Pay::apple()->payToken(['token' => $token]);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('applicationPrimaryAccountID'))->toEqual('123456');
    expect($result->get('totalAmount'))->toEqual('100');
    expect($result->get('_token_header'))->toEqual($token['header']);
});

