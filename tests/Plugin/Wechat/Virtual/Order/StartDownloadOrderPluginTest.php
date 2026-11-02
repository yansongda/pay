<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\StartDownloadOrderPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new StartDownloadOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'start_date' => '2024-01-01 00:00:00',
        'end_date' => '2024-01-01 23:59:59',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/start_download_order');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('start_date'))->toEqual('2024-01-01 00:00:00');
    expect($payload->get('end_date'))->toEqual('2024-01-01 23:59:59');
});

it('沙箱环境', function () {
    $plugin = new StartDownloadOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'env' => 1,
        'start_date' => '2024-01-01 00:00:00',
        'end_date' => '2024-01-01 23:59:59',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

it('携带 access token', function () {
    $plugin = new StartDownloadOrderPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'start_date' => '2024-01-01 00:00:00',
        'end_date' => '2024-01-01 23:59:59',
        'access_token' => 'test_access_token',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('access_token'))->toEqual('test_access_token');
});

