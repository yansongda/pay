<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\WebAccessTokenPlugin;

it('正常', function () {
    $plugin = new WebAccessTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams([
        'appid' => 'wx55955316af4ef13',
        'secret' => 'my_mp_app_secret',
        'code' => 'the_wechat_oauth_code',
    ]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toEqual('/sns/oauth2/access_token?'.http_build_query([
        'appid' => 'wx55955316af4ef13',
        'secret' => 'my_mp_app_secret',
        'code' => 'the_wechat_oauth_code',
        'grant_type' => 'authorization_code',
    ]));
    expect($payload->get('_body'))->toEqual('');
});

it('使用配置默认值', function () {
    $plugin = new WebAccessTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['code' => 'the_wechat_oauth_code']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toEqual('/sns/oauth2/access_token?'.http_build_query([
        'appid' => 'wx55955316af4ef13',
        'secret' => 'mp_app_secret_for_test',
        'code' => 'the_wechat_oauth_code',
        'grant_type' => 'authorization_code',
    ]));
    expect($payload->get('_body'))->toEqual('');
});

it('缺少 code 抛出异常', function () {
    $plugin = new WebAccessTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少微信网页授权参数 -- [code]', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少配置密钥抛出异常', function () {
    $plugin = new WebAccessTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams([
        '_config' => 'service_provider',
        'code' => 'the_wechat_oauth_code',
    ]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidConfigException::class, '配置异常: 缺少微信配置 -- [mp_app_secret] 或 [mp_app_id]', Exception::CONFIG_WECHAT_INVALID);

