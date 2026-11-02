<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\GetTitleUrlPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new GetTitleUrlPlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 获取抬头填写链接，缺少必要参数');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('普通参数', function () {
    $plugin = new GetTitleUrlPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
        'appid' => '1111',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/new-tax-control-fapiao/user-title/title-url?test=yansongda&appid=1111',
    ]);
});

it('正常', function () {
    $plugin = new GetTitleUrlPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'GET',
        '_url' => '/v3/new-tax-control-fapiao/user-title/title-url?test=yansongda&appid=wx55955316af4ef13',
    ]);
});

