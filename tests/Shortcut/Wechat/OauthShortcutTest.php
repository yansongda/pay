<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\Code2SessionPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\RefreshTokenPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\UserInfoPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\WebAccessTokenPlugin;
use Yansongda\Pay\Plugin\Wechat\Openapi\ResponsePlugin;
use Yansongda\Pay\Shortcut\Wechat\OauthShortcut;

it('网页授权 token', function () {
    $plugin = new OauthShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'web_token', 'code' => 'mock-code']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(WebAccessTokenPlugin::class);
    expect($plugins[2])->toBe(AddRadarPlugin::class);
    expect($plugins[3])->toBe(ResponsePlugin::class);
    expect($plugins[4])->toBe(ParserPlugin::class);
});

it('刷新', function () {
    $plugin = new OauthShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'refresh', 'refresh_token' => 'mock-refresh-token']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(RefreshTokenPlugin::class);
    expect($plugins[2])->toBe(AddRadarPlugin::class);
    expect($plugins[3])->toBe(ResponsePlugin::class);
    expect($plugins[4])->toBe(ParserPlugin::class);
});

it('用户信息', function () {
    $plugin = new OauthShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'userinfo', 'access_token' => 'mock-access-token', 'openid' => 'mock-openid']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(UserInfoPlugin::class);
    expect($plugins[2])->toBe(AddRadarPlugin::class);
    expect($plugins[3])->toBe(ResponsePlugin::class);
    expect($plugins[4])->toBe(ParserPlugin::class);
});

it('会话', function () {
    $plugin = new OauthShortcut();
    $plugins = $plugin->getPlugins(['_action' => 'session', 'js_code' => 'mock-js-code']);

    expect($plugins)->toBeArray();
    expect($plugins[0])->toBe(StartPlugin::class);
    expect($plugins[1])->toBe(Code2SessionPlugin::class);
    expect($plugins[2])->toBe(AddRadarPlugin::class);
    expect($plugins[3])->toBe(ResponsePlugin::class);
    expect($plugins[4])->toBe(ParserPlugin::class);
});

it('非法动作抛出异常', function () {
    $plugin = new OauthShortcut();

    $plugin->getPlugins(['_action' => 'invalid_action']);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_SHORTCUT_ACTION_INVALID);

