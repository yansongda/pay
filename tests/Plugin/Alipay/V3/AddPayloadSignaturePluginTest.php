<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\AddPayloadSignaturePlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $body = '{"out_trade_no":"yansongda","total_amount":"0.01"}';
    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3'])
        ->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/pay',
            '_body' => $body,
            'out_trade_no' => 'yansongda',
            'total_amount' => '0.01',
        ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $payload = $result->getPayload()->all();
    $authorization = $payload['_authorization'];

    // Authorization 格式: `ALIPAY-SHA256withRSA <authString>,sign=<base64>`
    [$scheme, $rest] = explode(' ', $authorization, 2);
    [$authString, $sign] = explode(',sign=', $rest, 2);
    expect($scheme)->toEqual('ALIPAY-SHA256withRSA');

    // authString: 顺序 app_id → app_cert_sn → nonce → timestamp(13 位毫秒)
    expect($authString)->toMatch('/^app_id=alipay_v3_test_app_id,app_cert_sn=[0-9a-f]+,nonce=[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12},timestamp=\d{13}$/');

    // 重建 5 行组串(body 非空 + appAuthToken 缺省第 5 行整行缺省),签名可被支付宝公钥证书验证
    $content = $authString."\n"
        .'POST'."\n"
        .'/v3/alipay/trade/pay'."\n"
        .$body."\n";
    $publicKey = openssl_pkey_get_public(file_get_contents(cert_path('alipay-v3/alipay_public_cert_test.crt')));
    expect(openssl_verify($content, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256))->toEqual(1);
});

it('无 body 的正常场景', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3'])
        ->setPayload(new Collection([
            '_method' => 'GET',
            '_url' => '/v3/alipay/trade/query?out_trade_no=yansongda',
        ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $authorization = $result->getPayload()->get('_authorization');
    [, $rest] = explode(' ', $authorization, 2);
    [$authString, $sign] = explode(',sign=', $rest, 2);

    // body 为空时保留空行,path 行含 query
    $content = $authString."\n"
        .'GET'."\n"
        .'/v3/alipay/trade/query?out_trade_no=yansongda'."\n"
        ."\n";
    $publicKey = openssl_pkey_get_public(file_get_contents(cert_path('alipay-v3/alipay_public_cert_test.crt')));
    expect(openssl_verify($content, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256))->toEqual(1);
});

it('app_auth_token 覆盖', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $body = '{"out_trade_no":"yansongda"}';
    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3', '_app_auth_token' => 'override_token'])
        ->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/pay',
            '_body' => $body,
        ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    // params `_app_auth_token` 优先,同时 merge 进 payload 供 AddRadarPlugin 输出 header
    expect($result->getPayload()->get('_app_auth_token'))->toEqual('override_token');

    // 组串含第 5 行 appAuthToken
    $authorization = $result->getPayload()->get('_authorization');
    [, $rest] = explode(' ', $authorization, 2);
    [$authString, $sign] = explode(',sign=', $rest, 2);
    $content = $authString."\n"
        .'POST'."\n"
        .'/v3/alipay/trade/pay'."\n"
        .$body."\n"
        .'override_token'."\n";
    $publicKey = openssl_pkey_get_public(file_get_contents(cert_path('alipay-v3/alipay_public_cert_test.crt')));
    expect(openssl_verify($content, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256))->toEqual(1);
});

it('app_auth_token 回退', function () {
    $plugin = new AddPayloadSignaturePlugin();
    Pay::config([
        'alipay' => [
            'alipay-v3-fallback' => [
                'app_id' => 'alipay_v3_test_app_id',
                'app_secret_cert' => cert_path('alipay-v3/app_secret_test.pem'),
                'app_public_cert_path' => cert_path('alipay-v3/alipay_public_cert_test.crt'),
                'alipay_public_cert_path' => cert_path('alipay-v3/alipay_public_cert_test.crt'),
                'app_auth_token' => 'config_fallback_token',
            ],
        ],
        '_force' => true,
    ]);

    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3-fallback'])
        ->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/pay',
            '_body' => '{"out_trade_no":"yansongda"}',
        ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    // params 未传时回落 config `app_auth_token`
    expect($result->getPayload()->get('_app_auth_token'))->toEqual('config_fallback_token');
});

it('不携带 app_auth_token', function () {
    $plugin = new AddPayloadSignaturePlugin();
    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3'])
        ->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/pay',
            '_body' => '{"out_trade_no":"yansongda"}',
        ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->all())->not->toHaveKey('_app_auth_token');
});

