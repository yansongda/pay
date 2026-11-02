<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\QueryPublishGoodsPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
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

it('sandbox env', function () {
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

