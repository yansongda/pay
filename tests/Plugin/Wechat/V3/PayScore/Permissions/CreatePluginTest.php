<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\CreatePlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 支付分预授权（签约），参数为空');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('service id missing', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'authorization_code' => 'AUTH_CODE',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
    $this->expectExceptionMessage('参数异常: 缺少支付分服务ID -- [service_id]');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('normal', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'authorization_code' => 'AUTH_CODE',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->all())->toEqual([
        '_method' => 'POST',
        '_url' => '/v3/payscore/permissions',
        'appid' => 'wx55955316af4ef13',
        'service_id' => '12345678901234567890123456789012',
        'notify_url' => 'https://pay.yansongda.cn',
        'authorization_code' => 'AUTH_CODE',
    ]);
});

it('payload params override config', function () {
    $plugin = new CreatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'authorization_code' => 'AUTH_CODE',
        'appid' => 'wx_payload_override',
        'service_id' => '99999',
        'notify_url' => 'https://notify.payload.cn',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('appid'))->toEqual('wx_payload_override');
    expect($result->getPayload()->get('service_id'))->toEqual('99999');
    expect($result->getPayload()->get('notify_url'))->toEqual('https://notify.payload.cn');
});

