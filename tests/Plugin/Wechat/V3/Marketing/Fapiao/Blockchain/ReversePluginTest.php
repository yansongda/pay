<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\ReversePlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new ReversePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 冲红电子发票，参数缺少 `fapiao_apply_id`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new ReversePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'fapiao_apply_id' => '111',
        'reason' => '222',
        '_t' => 'a',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/new-tax-control-fapiao/fapiao-applications/111/reverse',
        'reason' => '222',
    ]);
});

