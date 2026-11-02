<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\QueryPlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 查询电子发票，参数缺少 `fapiao_apply_id`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal params', function () {
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

it('normal', function () {
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

