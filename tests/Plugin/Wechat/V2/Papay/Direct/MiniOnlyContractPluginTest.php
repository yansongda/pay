<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V2\Papay\Direct\MiniOnlyContractPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new MiniOnlyContractPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_trade_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($result->getDirection())->toEqual(NoHttpRequestDirection::class);
    expect($payload->get('out_trade_no'))->toEqual('111');
    expect($payload->get('appid'))->toEqual('wx55955316af4ef13');
    expect($payload->get('mch_id'))->toEqual('1600314069');
    expect($payload->get('timestamp'))->not->toBeEmpty();
    expect($payload->all())->toHaveKey('notify_url');

    $destination = $result->getDestination();

    expect($destination->get('out_trade_no'))->toEqual('111');
    expect($destination->get('appid'))->toEqual('wx55955316af4ef13');
    expect($destination->get('mch_id'))->toEqual('1600314069');
    expect($destination->get('timestamp'))->not->toBeEmpty();
    expect($destination->all())->toHaveKey('notify_url');
});

