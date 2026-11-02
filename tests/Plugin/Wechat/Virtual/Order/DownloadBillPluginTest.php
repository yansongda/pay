<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\DownloadBillPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new DownloadBillPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'bill_date' => '2024-01-01',
        'bill_type' => 'ALL',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('_url'))->toEqual('/xpay/download_bill');
    expect($payload->get('env'))->toBeNull();
    expect($payload->get('bill_date'))->toEqual('2024-01-01');
    expect($payload->get('bill_type'))->toEqual('ALL');
});

it('沙箱环境', function () {
    $plugin = new DownloadBillPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'env' => 1,
        'bill_date' => '2024-01-01',
        'bill_type' => 'ALL',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('env'))->toEqual(1);
});

it('携带 access token', function () {
    $plugin = new DownloadBillPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'bill_date' => '2024-01-01',
        'bill_type' => 'ALL',
        'access_token' => 'test_access_token',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($payload->get('access_token'))->toEqual('test_access_token');
});

