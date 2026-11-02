<?php

declare(strict_types=1);

use Yansongda\Artful\Contract\PackerInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Pos\PayPlugin;
use Yansongda\Supports\Collection;

it('空 payload', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 付款码支付，参数为空', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('正常', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'description' => 'test',
        'out_trade_no' => '111',
        'payer' => [
            'auth_code' => '1234',
        ],
        'amount' => [
            'total' => 1,
        ],
        'scene_info' => [
            'id' => '5678',
        ],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($result->getPacker())->toEqual(PackerInterface::class);
    expect($payload->get('_url'))->toEqual('/v3/pay/transactions/codepay');
    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('appid'))->toEqual('wx55955316af4ef13');
    expect($payload->get('mchid'))->toEqual('1600314069');
    expect($payload->get('out_trade_no'))->toEqual('111');
    expect($payload->get('description'))->toEqual('test');
    expect($payload->get('payer')['auth_code'])->toEqual('1234');
    expect($payload->get('amount')['total'])->toEqual(1);
    expect($payload->get('scene_info')['id'])->toEqual('5678');
});

it('服务商', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'description' => 'test',
        'out_trade_no' => '111',
        'payer' => [
            'auth_code' => '1234',
        ],
        'amount' => [
            'total' => 1,
        ],
        'scene_info' => [
            'id' => '5678',
        ],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($result->getPacker())->toEqual(PackerInterface::class);
    expect($payload->get('_service_url'))->toEqual('/v3/pay/partner/transactions/codepay');
    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('sp_appid'))->toEqual('wx55955316af4ef13');
    expect($payload->get('sp_mchid'))->toEqual('1600314069');
    expect($payload->get('sub_mchid'))->toEqual('1600314070');
    expect($payload->get('out_trade_no'))->toEqual('111');
    expect($payload->get('description'))->toEqual('test');
    expect($payload->get('payer')['auth_code'])->toEqual('1234');
    expect($payload->get('amount')['total'])->toEqual(1);
    expect($payload->get('scene_info')['id'])->toEqual('5678');
});

it('服务商参数', function () {
    $plugin = new PayPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'sub_mchid' => 'aaaa',
        'description' => 'test',
        'out_trade_no' => '111',
        'payer' => [
            'auth_code' => '1234',
        ],
        'amount' => [
            'total' => 1,
        ],
        'scene_info' => [
            'id' => '5678',
        ],
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($result->getPacker())->toEqual(PackerInterface::class);
    expect($payload->get('_service_url'))->toEqual('/v3/pay/partner/transactions/codepay');
    expect($payload->get('_method'))->toEqual('POST');
    expect($payload->get('sp_appid'))->toEqual('wx55955316af4ef13');
    expect($payload->get('sp_mchid'))->toEqual('1600314069');
    expect($payload->get('sub_mchid'))->toEqual('aaaa');
    expect($payload->get('out_trade_no'))->toEqual('111');
    expect($payload->get('description'))->toEqual('test');
    expect($payload->get('payer')['auth_code'])->toEqual('1234');
    expect($payload->get('amount')['total'])->toEqual(1);
    expect($payload->get('scene_info')['id'])->toEqual('5678');
});

