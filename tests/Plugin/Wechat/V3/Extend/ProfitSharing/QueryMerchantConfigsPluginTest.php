<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\QueryMerchantConfigsPlugin;
use Yansongda\Supports\Collection;

it('模式错误', function () {
    $plugin = new QueryMerchantConfigsPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询最大分账比例，只支持服务商模式，当前配置为普通商户模式', Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE);

it('正常', function () {
    $plugin = new QueryMerchantConfigsPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_service_url' => '/v3/profitsharing/merchant-configs/1600314070',
    ]);
});

it('普通参数', function () {
    $plugin = new QueryMerchantConfigsPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])
        ->setPayload(new Collection([
            'sub_mch_id' => 'yansongda',
        ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_service_url' => '/v3/profitsharing/merchant-configs/yansongda',
    ]);
});

