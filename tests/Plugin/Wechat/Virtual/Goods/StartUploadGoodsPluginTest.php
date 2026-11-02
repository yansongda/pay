<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\StartUploadGoodsPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new StartUploadGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'group_id' => 'test_group_001',
        'goods_list' => [
            ['goods_id' => 'g001', 'goods_name' => '测试道具'],
        ],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/start_upload_goods');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('group_id'))->toEqual('test_group_001');
    expect($payload->get('goods_list'))->toEqual([
        ['goods_id' => 'g001', 'goods_name' => '测试道具'],
    ]);
});

it('沙箱环境', function () {
    $plugin = new StartUploadGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'group_id' => 'test_group_001',
        'goods_list' => [
            ['goods_id' => 'g001', 'goods_name' => '测试道具'],
        ],
        'env' => 1,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
    expect($payload->get('_url'))->toEqual('/xpay/start_upload_goods');
});

it('access token 透传', function () {
    $plugin = new StartUploadGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'group_id' => 'test_group_001',
        'goods_list' => [
            ['goods_id' => 'g001', 'goods_name' => '测试道具'],
        ],
        'access_token' => 'test_token',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('access_token'))->toEqual('test_token');
});

