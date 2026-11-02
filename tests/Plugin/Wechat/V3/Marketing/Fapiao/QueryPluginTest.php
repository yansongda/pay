<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\QueryPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 查询电子发票，参数缺少 `fapiao_apply_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('普通参数', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'fapiao_id' => 'yansongda',
        'fapiao_apply_id' => '111',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/new-tax-control-fapiao/fapiao-applications/111?fapiao_id=yansongda',
    ]);
});

it('正常', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'fapiao_apply_id' => '111',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/new-tax-control-fapiao/fapiao-applications/111',
    ]);
});

