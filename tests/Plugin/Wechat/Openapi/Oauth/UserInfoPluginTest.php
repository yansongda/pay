<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\UserInfoPlugin;

it('正常', function () {
    $plugin = new UserInfoPlugin();
    $rocket = new Rocket();
    $rocket->setParams([
        'access_token' => 'the_wechat_access_token',
        'openid' => 'the_wechat_openid',
        'lang' => 'en',
    ]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toEqual('/sns/userinfo?'.http_build_query([
        'access_token' => 'the_wechat_access_token',
        'openid' => 'the_wechat_openid',
        'lang' => 'en',
    ]));
    expect($payload->get('_body'))->toEqual('');
});

it('默认语言', function () {
    $plugin = new UserInfoPlugin();
    $rocket = new Rocket();
    $rocket->setParams([
        'access_token' => 'the_wechat_access_token',
        'openid' => 'the_wechat_openid',
    ]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toEqual('/sns/userinfo?'.http_build_query([
        'access_token' => 'the_wechat_access_token',
        'openid' => 'the_wechat_openid',
        'lang' => 'zh_CN',
    ]));
    expect($payload->get('_url'))->toContain('lang=zh_CN');
    expect($payload->get('_body'))->toEqual('');
});

it('缺少 access token 抛出异常', function () {
    $plugin = new UserInfoPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['openid' => 'the_wechat_openid']);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少微信网页授权参数 -- [access_token]', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少 openid 抛出异常', function () {
    $plugin = new UserInfoPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['access_token' => 'the_wechat_access_token']);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少微信网页授权参数 -- [openid]', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

