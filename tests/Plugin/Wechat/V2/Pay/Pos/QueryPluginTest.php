<?php

declare(strict_types=1);

use Yansongda\Artful\Packer\XmlPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Pos\QueryPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new QueryPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'out_trade_no' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($result->getPacker())->toEqual(XmlPacker::class);
    expect($payload->get('_url'))->toEqual('/pay/orderquery');
    expect($payload->get('_content_type'))->toEqual('application/xml');
    expect($payload->get('out_trade_no'))->toEqual('111');
    expect($payload->get('appid'))->toEqual('wx55955316af4ef13');
    expect($payload->get('mch_id'))->toEqual('1600314069');
    expect($payload->get('sign_type'))->toEqual('MD5');
    expect($payload->get('nonce_str'))->not->toBeEmpty();
});

