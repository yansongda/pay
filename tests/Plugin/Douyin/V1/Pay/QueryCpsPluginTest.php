<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryCpsPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryCpsPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);
    $rocket->setPayload(new Collection(['out_order_no' => '20260905123456']));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    expect($payload?->get('_method'))->toBe('POST');
    expect($payload?->get('_url'))->toBe('/api/trade_basic/v1/developer/query_cps/');
    // 业务字段透传保留，除 `_method`/`_url` 外不新增任何键（尤其无 app_id）
    expect($payload?->all())->toBe([
        'out_order_no' => '20260905123456',
        '_method' => 'POST',
        '_url' => '/api/trade_basic/v1/developer/query_cps/',
    ]);
});

it('空 payload', function () {
    $plugin = new QueryCpsPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $payload = $result->getPayload();

    expect($payload?->all())->toBe([
        '_method' => 'POST',
        '_url' => '/api/trade_basic/v1/developer/query_cps/',
    ]);
});

