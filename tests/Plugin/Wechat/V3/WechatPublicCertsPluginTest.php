<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\WechatPublicCertsPlugin;

it('正常', function () {
    $plugin = new WechatPublicCertsPlugin();
    $rocket = (new Rocket())->setParams(['aaa' => 'bbb'])->setPayload(['name' => 'yansongda']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload()->all();

    expect($payload['_method'])->toEqual('GET');
    expect($payload['_url'])->toEqual('/v3/certificates');
    expect($payload)->not->toHaveKey('aaa');
    expect($payload)->not->toHaveKey('name');
});

