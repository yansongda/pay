<?php

declare(strict_types=1);

use Yansongda\Artful\Packer\XmlPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Unipay\Qra\Scan\RefundPlugin;
use Yansongda\Supports\Str;

it('正常', function () {
    $plugin = new RefundPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'qra']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    $payload = $result->getPayload();

    expect($result->getPacker())->toEqual(XmlPacker::class);
    expect($payload->get('_url'))->toEqual('https://qra.95516.com/pay/gateway');
    expect($payload->get('service'))->toEqual('unified.trade.refund');
    expect($payload->get('charset'))->toEqual('UTF-8');
    expect($payload->get('sign_type'))->toEqual('MD5');
    expect($payload->get('mch_id'))->toEqual('QRA29045311KKR1');
    expect(Str::length($payload->get('nonce_str')))->toEqual(32);
});

