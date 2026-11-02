<?php

declare(strict_types=1);

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
use Yansongda\Pay\Plugin\Apple\Pay\MerchantSessionPlugin;
use Yansongda\Pay\Plugin\Apple\ResponsePlugin;
use Yansongda\Pay\Shortcut\Apple\MerchantSessionShortcut;

it('获取插件列表', function () {
    $shortcut = new MerchantSessionShortcut();
    expect($shortcut->getPlugins([]))->toEqual([
        StartPlugin::class,
        MerchantSessionPlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        ParserPlugin::class,
    ]);
});

it('rocket 直连安装配合 AddRadar 插件', function () {
    $url = 'https://apple-pay-gateway.apple.com/paymentservices/startSession';

    $rocket = (new Rocket())->setParams([
        'validation_url' => $url,
        'display_name' => 'Yansongda Store',
        'initiative_context' => 'https://shop.yansongda.cn',
    ]);

    $result = (new MerchantSessionPlugin())->assembly(
        $rocket,
        fn ($rocket) => (new AddRadarPlugin())->assembly($rocket, fn ($rocket) => $rocket)
    );

    $request = $result->getRadar();

    expect($request->getMethod())->toEqual('POST');
    expect((string) $request->getUri())->toEqual($url);
    expect($request->hasHeader('Authorization'))->toBeFalse();
    expect($request->getHeaderLine('Content-Type'))->toEqual('application/json');

    $body = json_decode((string) $request->getBody(), true);
    expect($body['merchantIdentifier'])->toEqual('merchant.com.yansongda.pay');
    expect($body['displayName'])->toEqual('Yansongda Store');
    expect($body['initiative'])->toEqual('web');
    expect($body['initiativeContext'])->toEqual('https://shop.yansongda.cn');
});
it('HTTP mock 下的商户会话端到端', function () {
    $http = \Mockery::mock(\GuzzleHttp\Client::class);
    $captured = null;
    $http->shouldReceive('sendRequest')->once()->with(\Mockery::on(function ($request) use (&$captured) {
        $captured = $request;

        return true;
    }))->andReturn(new \GuzzleHttp\Psr7\Response(200, [], (string) json_encode([
        'merchantSessionIdentifier' => 'mock-session',
        'nonce' => 'mock-nonce',
        'epochTimestamp' => 1,
        'expiresAt' => 2,
        'merchantIdentifier' => 'merchant.com.yansongda.pay',
        'domainName' => 'example.com',
        'signature' => 'mock-signature',
    ])));

    \Yansongda\Pay\Pay::set(\Yansongda\Artful\Contract\HttpClientInterface::class, $http);

    $result = \Yansongda\Pay\Pay::apple()->merchantSession([
        'validation_url' => 'https://apple-pay-gateway-cert.apple.com/paymentSession',
        'display_name' => '示例商户',
        'initiative_context' => 'https://example.com',
    ]);

    expect((string) $captured->getUri())->toBe('https://apple-pay-gateway-cert.apple.com/paymentSession');
    expect($captured->getHeaderLine('Authorization'))->toBe('');
    expect($captured->getHeaderLine('Content-Type'))->toBe('application/json');

    $body = (array) json_decode((string) $captured->getBody(), true);
    expect($body['merchantIdentifier'])->toBe('merchant.com.yansongda.pay');
    expect($body['displayName'])->toBe('示例商户');
    expect($body['initiative'])->toBe('web');
    expect($body['initiativeContext'])->toBe('https://example.com');

    expect($result)->toBeInstanceOf(\Yansongda\Supports\Collection::class);
    expect($result->get('merchantSessionIdentifier'))->toBe('mock-session');

    \Mockery::close();
});

