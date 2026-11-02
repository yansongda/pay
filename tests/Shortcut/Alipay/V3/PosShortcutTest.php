<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\PosPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\V3\PosShortcut;

it('默认', function () {
    $plugin = new PosShortcut();
    $plugins = $plugin->getPlugins([]);

    expect($plugins)->toEqual([
        StartPlugin::class,
        PosPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);

    // post 阶段按数组逆序执行：`ResponsePlugin` 必须位于 `VerifySignaturePlugin` 之前（数组序更靠前），
    // 使 post 阶段先验签（200 强制/其余有签才验，防篡改错误体）再由 `ResponsePlugin` 抛业务异常
    expect(array_search(ResponsePlugin::class, $plugins, true))->toBeLessThan(array_search(VerifySignaturePlugin::class, $plugins, true));
});

it('端到端', function () {
    $plugin = new PosShortcut();
    $responseData = [
        'trade_no' => '2023122122001499160501589436',
        'out_trade_no' => 'v3pos1704093802',
        'total_amount' => '0.01',
    ];
    $body = json_encode($responseData);
    $timestamp = (string) (int) (microtime(true) * 1000);
    $nonce = 'yansongda-nonce';

    openssl_sign(
        $timestamp."\n".$nonce."\n".$body."\n",
        $sign,
        openssl_pkey_get_private(file_get_contents(cert_path('alipay-v3/app_secret_test.pem'))),
        OPENSSL_ALGO_SHA256
    );

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn(new Response(200, [
        'alipay-timestamp' => $timestamp,
        'alipay-nonce' => $nonce,
        'alipay-signature' => base64_encode($sign),
        'alipay-sn' => CertManager::alipayGetAppCertSn(cert_path('alipay-v3/alipay_public_cert_test.crt')),
    ], $body));
    Pay::set(HttpClientInterface::class, $http);

    $result = Artful::artful($plugin->getPlugins([]), [
        '_config' => 'alipay-v3',
        'out_trade_no' => 'v3pos1704093802',
        'total_amount' => '0.01',
        'subject' => 'yansongda 测试 - V3 Pos',
        'scene' => 'bar_code',
        'auth_code' => '286958267789018980',
        '_return_rocket' => true,
    ]);

    $radar = $result->getRadar();

    expect($radar->getMethod())->toEqual('POST');
    expect((string) $radar->getUri())->toEqual('https://openapi.alipay.com/v3/alipay/trade/pay');

    $requestBody = json_decode((string) $radar->getBody(), true);
    expect($requestBody['out_trade_no'])->toBe('v3pos1704093802');
    expect($requestBody['total_amount'])->toBe('0.01');
    expect($requestBody['scene'])->toBe('bar_code');
    expect($requestBody['auth_code'])->toBe('286958267789018980');

    expect($result->getDestination()->all())->toEqualCanonicalizing($responseData);
});

