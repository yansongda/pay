<?php

declare(strict_types=1);

use Yansongda\Artful\Packer\XmlPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Redpack\SendPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new SendPlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'mch_billno' => '111',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($result->getPacker())->toEqual(XmlPacker::class);
    expect($payload->get('_url'))->toEqual('/mmpaymkttransfers/sendredpack');
    expect($payload->get('_content_type'))->toEqual('application/xml');
    expect($payload->get('mch_billno'))->toEqual('111');
    expect($payload->get('wxappid'))->toEqual('wx55955316af4ef13');
    expect($payload->get('mch_id'))->toEqual('1600314069');
    expect($payload->get('nonce_str'))->not->toBeEmpty();
});

