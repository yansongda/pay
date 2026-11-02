<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Supports\Collection;

it('正常', function () {
    $plugin = new AddRadarPlugin();
    $body = '{"out_trade_no":"yansongda","total_amount":"0.01"}';
    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3'])
        ->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/pay',
            '_body' => $body,
            '_authorization' => 'ALIPAY-SHA256withRSA app_id=alipay_v3_test_app_id,nonce=test,timestamp=123,sign=abc',
            '_app_auth_token' => 'test_token',
        ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);
    $radar = $result->getRadar();

    expect($radar->getMethod())->toEqual('POST');
    expect($radar->getUri()->__toString())->toEqual('https://openapi.alipay.com/v3/alipay/trade/pay');
    expect($radar->getBody()->getContents())->toEqual($body);

    $headers = $radar->getHeaders();
    expect($headers['Authorization'][0])->toEqual('ALIPAY-SHA256withRSA app_id=alipay_v3_test_app_id,nonce=test,timestamp=123,sign=abc');
    expect($headers['Content-Type'][0])->toEqual('application/json; charset=utf-8');
    expect($headers['Accept'][0])->toEqual('application/json, text/plain, application/x-gzip');
    expect($headers['User-Agent'][0])->toEqual('yansongda/pay-v3');
    expect($headers['alipay-app-auth-token'][0])->toEqual('test_token');

    // alipay-request-id 每请求必带且为 UUID
    expect($headers)->toHaveKey('alipay-request-id');
    expect($headers['alipay-request-id'][0])->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

it('不携带 app_auth_token', function () {
    $plugin = new AddRadarPlugin();
    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3'])
        ->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/pay',
            '_body' => '{"out_trade_no":"yansongda"}',
            '_authorization' => 'ALIPAY-SHA256withRSA app_id=alipay_v3_test_app_id,nonce=test,timestamp=123,sign=abc',
        ]));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $headers = $result->getRadar()->getHeaders();
    expect($headers)->toHaveKey('Authorization');
    expect($headers)->not->toHaveKey('alipay-app-auth-token');
});

it('body 排除下划线键', function () {
    $plugin = new AddRadarPlugin();
    // 模拟真实管道: AddPayloadBodyPlugin 通过 filter_params 生成 `_body`,`_` 前缀键不进 body
    $rocket = (new Rocket())
        ->setParams(['_config' => 'alipay-v3'])
        ->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/pay',
            '_authorization' => 'ALIPAY-SHA256withRSA app_id=alipay_v3_test_app_id,nonce=test,timestamp=123,sign=abc',
            '_app_auth_token' => 'test_token',
            'out_trade_no' => 'yansongda',
            'total_amount' => '0.01',
        ]));

    $rocket = (new AddPayloadBodyPlugin())->assembly($rocket, fn ($rocket) => $rocket);
    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    $bodyJson = json_decode($result->getRadar()->getBody()->getContents(), true);

    expect($bodyJson)->toEqual(['out_trade_no' => 'yansongda', 'total_amount' => '0.01']);
    foreach (array_keys($bodyJson) as $key) {
        expect((string) $key)->not->toStartWith('_');
    }
});

