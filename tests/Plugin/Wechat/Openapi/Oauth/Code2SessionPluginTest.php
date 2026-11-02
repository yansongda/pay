<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\Code2SessionPlugin;

it('正常', function () {
    $plugin = new Code2SessionPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['appid' => 'wx1234567890', 'secret' => 'test_secret', 'js_code' => 'test_js_code']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toEqual('/sns/jscode2session?appid=wx1234567890&secret=test_secret&js_code=test_js_code&grant_type=authorization_code');
    expect($payload->get('_body'))->toEqual('');
});

it('使用配置默认值', function () {
    $plugin = new Code2SessionPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['js_code' => 'test_js_code']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toContain('appid=wx55955316af4ef14');
    expect($payload->get('_url'))->toContain('secret=mini_app_secret_for_test');
    expect($payload->get('_url'))->toContain('js_code=test_js_code');
    expect($payload->get('_url'))->toContain('grant_type=authorization_code');
});

it('缺少 js code 抛出异常', function () {
    $plugin = new Code2SessionPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['appid' => 'wx1234567890', 'secret' => 'test_secret']);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('参数密钥覆盖配置', function () {
    $plugin = new Code2SessionPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['appid' => 'wx1234567890', 'secret' => 'params_secret_override', 'js_code' => 'test_js_code']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_url'))->toContain('secret=params_secret_override');
    expect($payload->get('_url'))->not->toContain('mini_app_secret_for_test');
});

