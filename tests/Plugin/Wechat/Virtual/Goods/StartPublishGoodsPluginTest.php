<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\StartPublishGoodsPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new StartPublishGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'group_id' => 'test_group_001',
        'upload_task_id' => 'task_123456',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/start_publish_goods');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('group_id'))->toEqual('test_group_001');
    expect($payload->get('upload_task_id'))->toEqual('task_123456');
});

it('沙箱环境', function () {
    $plugin = new StartPublishGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'group_id' => 'test_group_001',
        'upload_task_id' => 'task_123456',
        'env' => 1,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
    expect($payload->get('_url'))->toEqual('/xpay/start_publish_goods');
});

