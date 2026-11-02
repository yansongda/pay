<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization\TokenAppPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new TokenAppPlugin();
    $params = ['grant_type' => 'authorization_code', 'code' => '123456'];

    $rocket = (new Rocket())
        ->setParams($params)
        ->setPayload(new Collection(['app_auth_token' => 'xxx']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($payload->get('method'))->toEqual('alipay.open.auth.token.app');
    expect($payload->get('biz_content'))->toEqual($params);
    expect($payload->has('app_auth_token'))->toBeFalse();
});

