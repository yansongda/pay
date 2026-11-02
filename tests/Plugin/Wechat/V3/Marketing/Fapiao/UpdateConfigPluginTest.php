<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\UpdateConfigPlugin;
use Yansongda\Supports\Collection;

it('普通参数', function () {
    $plugin = new UpdateConfigPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'test' => 'yansongda',
        'card_appid' => '1111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'PATCH',
        '_url' => '/v3/new-tax-control-fapiao/merchant/development-config',
        'test' => 'yansongda',
        'card_appid' => '1111',
    ]);
});

