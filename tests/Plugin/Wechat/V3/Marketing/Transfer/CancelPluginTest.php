<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\CancelPlugin;
use Yansongda\Supports\Collection;

it('模式错误', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider']);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 撤销转账，只支持普通商户模式，当前配置为服务商模式', Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);

it('空 payload', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 撤销转账，参数缺少 `out_bill_no`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new CancelPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_bill_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/fund-app/mch-transfer/transfer-bills/out-bill-no/111/cancel',
    ]);
});

