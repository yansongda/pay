<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\QueryUploadGoodsPlugin;
use Yansongda\Supports\Collection;

it('normal', function () {
    $plugin = new QueryUploadGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'upload_task_id' => 'task_123456',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/query_upload_goods');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('upload_task_id'))->toEqual('task_123456');
});

it('sandbox env', function () {
    $plugin = new QueryUploadGoodsPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'upload_task_id' => 'task_123456',
        'env' => 1,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
    expect($payload->get('_url'))->toEqual('/xpay/query_upload_goods');
});

