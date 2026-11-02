<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\InvokeJsapiPlugin;
use Yansongda\Supports\Collection;

it('mode wrong', function () {
    $plugin = new InvokeJsapiPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider']);

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);
    $this->expectExceptionMessage('参数异常: JSAPI调起用户确认收款，只支持普通商户模式，当前配置为服务商模式');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('missing package', function () {
    $plugin = new InvokeJsapiPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_MISSING_NECESSARY_PARAMS);
    $this->expectExceptionMessage('JSAPI调起用户确认收款失败：响应缺少 `package_info` 参数，请自行检查参数是否符合微信要求');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
    $plugin = new InvokeJsapiPlugin();
    $rocket = (new Rocket())
        ->setDestination(new Collection(['package_info' => 'yansongda']))
        ->setPayload(['_invoke_appId' => '111']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->get('appId'))->toEqual('111');
    expect($contents->get('package'))->toEqual('yansongda');
    expect($contents->get('mchId'))->toEqual('1600314069');
});

it('normal', function () {
    $plugin = new InvokeJsapiPlugin();
    $rocket = (new Rocket())->setDestination(new Collection(['package_info' => 'yansongda']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->get('appId'))->toEqual('wx55955316af4ef13');
    expect($contents->get('package'))->toEqual('yansongda');
    expect($contents->get('mchId'))->toEqual('1600314069');
});

