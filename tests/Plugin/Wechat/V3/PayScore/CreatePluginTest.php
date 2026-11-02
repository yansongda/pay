<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\CreatePlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 创建支付分订单，参数为空', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
        'service_introduction' => '停车服务',
        'time_range' => ['start_time' => '20240910100000'],
        'risk_fund' => ['name' => 'DEPOSIT', 'amount' => 100],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder',
        'appid' => 'wx55955316af4ef13',
        'service_id' => '12345678901234567890123456789012',
        'notify_url' => 'https://pay.yansongda.cn',
        'out_order_no' => '202409101234567890',
        'service_introduction' => '停车服务',
        'time_range' => ['start_time' => '20240910100000'],
        'risk_fund' => ['name' => 'DEPOSIT', 'amount' => 100],
    ]);

    expect($result->getPayload()->all())->not->toHaveKey('mchid');
});

it('显式参数覆盖配置', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
        'appid' => 'wx55955316af4ef99',
        'service_id' => '12345678901234567890123456789999',
        'notify_url' => 'https://pay.yansongda.cn/payscore/notify',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder',
        'appid' => 'wx55955316af4ef99',
        'service_id' => '12345678901234567890123456789999',
        'notify_url' => 'https://pay.yansongda.cn/payscore/notify',
        'out_order_no' => '202409101234567890',
    ]);
});

it('小程序类型', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_type' => 'mini'])->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/payscore/serviceorder',
        'appid' => 'wx55955316af4ef14',
        'service_id' => '12345678901234567890123456789012',
        'notify_url' => 'https://pay.yansongda.cn',
        'out_order_no' => '202409101234567890',
    ]);
});

it('缺少服务商 ID', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少支付分服务ID -- [service_id]', Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);

it('缺少 app_id', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
        'appid' => '',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少公众账号ID -- [appid]', Exception::PARAMS_WECHAT_APPID_MISSING);

it('服务商模式被拒绝', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);

