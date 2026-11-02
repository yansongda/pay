<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\GetTaxCodePlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new GetTaxCodePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 获取商户可开具的商品和服务税收分类编码对照表，缺少 `offset` 或 `limit` 参数', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new GetTaxCodePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'offset' => 'yansongda',
        'limit' => '111',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/new-tax-control-fapiao/merchant/tax-codes?offset=yansongda&limit=111',
    ]);
});

