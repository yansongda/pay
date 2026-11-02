<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\V3\QueryShortcut;
use Yansongda\Pay\Tests\Support\Traits\AlipayV3SignedResponseHelpers;

uses(AlipayV3SignedResponseHelpers::class);

it('正常', function () {
    $shortcut = new QueryShortcut();
    $plugins = $shortcut->getPlugins([]);

    expect($plugins)->toEqual([
        StartPlugin::class,
        QueryPlugin::class,
        AddPayloadBodyPlugin::class,
        AddPayloadSignaturePlugin::class,
        AddRadarPlugin::class,
        ResponsePlugin::class,
        VerifySignaturePlugin::class,
        ParserPlugin::class,
    ]);

    // 管道 post 阶段逆序执行：ResponsePlugin 必须位于 VerifySignaturePlugin 之前，
    // 使非 2xx 响应先经验签（有签才验）再抛业务异常
    expect(array_search(VerifySignaturePlugin::class, $plugins, true))->toBeGreaterThan(array_search(ResponsePlugin::class, $plugins, true));
});

it('HTTP 查询', function () {
    $shortcut = new QueryShortcut();
    $body = json_encode([
        'trade_no' => '2026090222001400001234567890',
        'out_trade_no' => 'yansongda-2026',
        'trade_status' => 'TRADE_SUCCESS',
        'total_amount' => '10.00',
    ], JSON_UNESCAPED_SLASHES);

    $captured = null;
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->withArgs(function ($request) use (&$captured) {
        $captured = $request;

        return true;
    })->andReturn($this->makeSignedResponse(200, $body));
    Pay::set(HttpClientInterface::class, $http);

    $result = Artful::artful($shortcut->getPlugins([]), ['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026']);

    expect($captured)->toBeInstanceOf(Request::class);
    expect($captured->getMethod())->toEqual('POST');
    expect((string) $captured->getUri())->toEqual('https://openapi.alipay.com/v3/alipay/trade/query');
    expect((string) $captured->getBody())->toContain('"out_trade_no":"yansongda-2026"');

    expect($result->get('out_trade_no'))->toEqual('yansongda-2026');
    expect($result->get('trade_status'))->toEqual('TRADE_SUCCESS');
});
