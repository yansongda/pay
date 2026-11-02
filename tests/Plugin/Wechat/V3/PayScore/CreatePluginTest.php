<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\CreatePlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 创建支付分订单，参数为空');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal', function () {
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

it('explicit params override config', function () {
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

it('mini type', function () {
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

it('service id missing', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
    $this->expectExceptionMessage('参数异常: 缺少支付分服务ID -- [service_id]');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('app id missing', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
        'appid' => '',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_WECHAT_APPID_MISSING);
    $this->expectExceptionMessage('参数异常: 缺少公众账号ID -- [appid]');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('service mode rejected', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'out_order_no' => '202409101234567890',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

