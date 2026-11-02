<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\QueryDownloadOrderPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryDownloadOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'download_task_id' => '1234567890',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/query_download_order');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('download_task_id'))->toEqual('1234567890');
});

it('沙箱环境', function () {
    $plugin = new QueryDownloadOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'download_task_id' => '1234567890',
        'env' => 1,
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

it('携带 access token', function () {
    $plugin = new QueryDownloadOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'download_task_id' => '1234567890',
        'access_token' => 'test_access_token',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('access_token'))->toEqual('test_access_token');
});

