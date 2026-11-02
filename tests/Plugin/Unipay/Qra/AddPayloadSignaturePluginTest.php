<?php

declare(strict_types=1);

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Unipay\Qra\AddPayloadSignaturePlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $payload = [
        'out_trade_no' => 'pos-qra-20240106163401',
        'body' => '测试商品',
        'total_fee' => 1,
        'mch_create_ip' => '127.0.0.1',
        'auth_code' => '131969896307360385',
        'op_device_id' => '123',
        'terminal_info' => json_encode([
            'device_type' => '07',
            'terminal_id' => '123',
        ]),
        'service' => 'unified.trade.micropay',
        'charset' => 'UTF-8',
        'sign_type' => 'MD5',
        'mch_id' => 'QRA29045311KKR1',
        'nonce_str' => 'UhxOr4kzerPGku9wCaVQyfd1zisoAnAm',
        '_aaa' => 'bb',
        'a' => null,
    ];

    $rocket = (new Rocket())->setParams(['_config' => 'qra'])->setPayload($payload);
    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    $payload = $result->getPayload();

    expect($payload->get('sign'))->toEqual('DB571C2F75C657B42485CD07470F0FB9');
});

it('payload 为空', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $rocket = (new Rocket())->setPayload(new Collection());

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_NECESSARY_PARAMS_MISSING);

