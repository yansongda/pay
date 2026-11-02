<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Open\Authorization\TokenAppQueryPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new TokenAppQueryPlugin();
    $params = ['grant_type' => 'authorization_code', 'code' => '123456'];

    $rocket = (new Rocket())
        ->setParams($params)
        ->setPayload(new Collection(['app_auth_token' => 'xxx']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($payload->get('method'))->toEqual('alipay.open.auth.token.app.query');
    expect($payload->get('biz_content'))->toEqual($params);
    expect($payload->has('app_auth_token'))->toBeFalse();
});

it('携带 action 的正常场景', function () {
    $plugin = new TokenAppQueryPlugin();
    $params = ['grant_type' => 'authorization_code', 'code' => '123456', '_action' => 'query'];

    $rocket = (new Rocket())
        ->setParams($params)
        ->setPayload(new Collection(['app_auth_token' => 'xxx']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($result->getDirection())->not->toEqual(ResponseDirection::class);
    expect($payload->get('method'))->toEqual('alipay.open.auth.token.app.query');
    expect($payload->get('biz_content'))->toEqual($params);
    expect($payload->get('biz_content')['_action'])->toEqual('query');
    expect($payload->has('app_auth_token'))->toBeFalse();
});

