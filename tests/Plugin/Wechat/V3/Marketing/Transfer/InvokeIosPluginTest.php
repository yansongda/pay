<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\InvokeIosPlugin;
use Yansongda\Supports\Collection;

it('模式错误', function () {
    $plugin = new InvokeIosPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider']);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: iOS调起用户确认收款，只支持普通商户模式，当前配置为服务商模式', Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);

it('缺少 package', function () {
    $plugin = new InvokeIosPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidResponseException::class, 'iOS调起用户确认收款失败：响应缺少 `package_info` 参数，请自行检查参数是否符合微信要求', Exception::RESPONSE_MISSING_NECESSARY_PARAMS);

it('普通参数', function () {
    $plugin = new InvokeIosPlugin();
    $rocket = (new Rocket())
        ->setDestination(new Collection(['package_info' => 'affffddafdfafddffda==']))
        ->setPayload(['_invoke_appId' => '111']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->get('businessType'))->toEqual('requestMerchantTransfer');
    expect($contents->get('query'))->toEqual('appId=111&mchId=1600314069&package=affffddafdfafddffda%3D%3D');
});

it('正常', function () {
    $plugin = new InvokeIosPlugin();
    $rocket = (new Rocket())->setDestination(new Collection(['package_info' => 'affffddafdfafddffda==']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $contents = $result->getDestination();

    expect($contents->get('businessType'))->toEqual('requestMerchantTransfer');
    expect($contents->get('query'))->toEqual('appId=wx55955316af4ef13&mchId=1600314069&package=affffddafdfafddffda%3D%3D');
});

