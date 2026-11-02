<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Openapi\Oauth\Code2SessionPlugin;

it('normal', function () {
    $plugin = new Code2SessionPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['appid' => 'wx1234567890', 'secret' => 'test_secret', 'js_code' => 'test_js_code']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('GET');
    expect($payload->get('_url'))->toEqual('/sns/jscode2session?appid=wx1234567890&secret=test_secret&js_code=test_js_code&grant_type=authorization_code');
    expect($payload->get('_body'))->toEqual('');
});

it('with config defaults', function () {
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

it('missing js code throws', function () {
    $plugin = new Code2SessionPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['appid' => 'wx1234567890', 'secret' => 'test_secret']);

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('params secret overrides config', function () {
    $plugin = new Code2SessionPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['appid' => 'wx1234567890', 'secret' => 'params_secret_override', 'js_code' => 'test_js_code']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('_url'))->toContain('secret=params_secret_override');
    expect($payload->get('_url'))->not->toContain('mini_app_secret_for_test');
});

