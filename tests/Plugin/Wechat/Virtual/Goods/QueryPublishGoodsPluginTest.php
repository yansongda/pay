<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\QueryPublishGoodsPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryPublishGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'publish_task_id' => 'pub_task_789',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/query_publish_goods');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('publish_task_id'))->toEqual('pub_task_789');
});

it('沙箱环境', function () {
    $plugin = new QueryPublishGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'publish_task_id' => 'pub_task_789',
        'env' => 1,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
    expect($payload->get('_url'))->toEqual('/xpay/query_publish_goods');
});

