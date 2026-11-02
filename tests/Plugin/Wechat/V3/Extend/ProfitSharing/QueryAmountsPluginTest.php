<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\QueryAmountsPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryAmountsPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询剩余待分金额，参数缺少 `transaction_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new QueryAmountsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'transaction_id' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/profitsharing/transactions/yansongda/amounts',
        '_service_url' => '/v3/profitsharing/transactions/yansongda/amounts',
    ]);
});

