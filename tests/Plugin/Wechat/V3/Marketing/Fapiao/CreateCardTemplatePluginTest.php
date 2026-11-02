<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\CreateCardTemplatePlugin;
use Yansongda\Supports\Collection;

it('普通参数', function () {
    $plugin = new CreateCardTemplatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
        'card_appid' => '1111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/new-tax-control-fapiao/card-template',
        'test' => 'yansongda',
        'card_appid' => '1111',
    ]);
});

it('正常', function () {
    $plugin = new CreateCardTemplatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/new-tax-control-fapiao/card-template',
        'test' => 'yansongda',
        'card_appid' => 'wx55955316af4ef13',
    ]);
});

