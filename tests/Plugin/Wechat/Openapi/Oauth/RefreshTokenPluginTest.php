<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\RefreshTokenPlugin;

it('正常', function () {
    $plugin = new RefreshTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams([
        'appid' => 'wx55955316af4ef13',
        'refresh_token' => 'the_wechat_refresh_token',
    ]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toEqual('/sns/oauth2/refresh_token?'.http_build_query([
        'appid' => 'wx55955316af4ef13',
        'grant_type' => 'refresh_token',
        'refresh_token' => 'the_wechat_refresh_token',
    ]));
    expect($payload->get('_body'))->toEqual('');
});

it('使用配置默认值', function () {
    $plugin = new RefreshTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['refresh_token' => 'the_wechat_refresh_token']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toEqual('/sns/oauth2/refresh_token?'.http_build_query([
        'appid' => 'wx55955316af4ef13',
        'grant_type' => 'refresh_token',
        'refresh_token' => 'the_wechat_refresh_token',
    ]));
    expect($payload->get('_body'))->toEqual('');
});

it('缺少 refresh token 抛出异常', function () {
    $plugin = new RefreshTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少微信网页授权参数 -- [refresh_token]', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少配置 app_id 抛出异常', function () {
    $plugin = new RefreshTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams([
        'appid' => '',
        'refresh_token' => 'the_wechat_refresh_token',
    ]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidConfigException::class, '配置异常: 缺少微信配置 -- [mp_app_id]', Exception::CONFIG_WECHAT_INVALID);

