<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization\TokenPlugin;

it('正常', function () {
    $plugin = new TokenPlugin();
    $rocket = (new Rocket())
        ->setParams(['name' => 'yansongda']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($payload->get('method'))->toEqual('alipay.system.oauth.token');
    expect($payload->get('name'))->toEqual('yansongda');
    expect($payload->has('biz_content'))->toBeFalse();
});

