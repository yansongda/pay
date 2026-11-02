<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\V3\RefundShortcut;
use Yansongda\Pay\Tests\Support\Traits\AlipayV3SignedResponseHelpers;

uses(AlipayV3SignedResponseHelpers::class);

it('正常', function () {
    $shortcut = new RefundShortcut();
    $plugins = $shortcut->getPlugins([]);

    expect($plugins)->toEqual([
        StartPlugin::class,
        RefundPlugin::class,
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

it('HTTP 退款', function () {
    $shortcut = new RefundShortcut();
    $body = json_encode([
        'trade_no' => '2026090222001400001234567890',
        'out_trade_no' => 'yansongda-2026',
        'out_request_no' => 'yansongda-2026-refund',
        'refund_fee' => '3.00',
        'fund_change' => 'Y',
    ], JSON_UNESCAPED_SLASHES);

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($this->makeSignedResponse(200, $body));
    Pay::set(HttpClientInterface::class, $http);

    $result = Artful::artful($shortcut->getPlugins([]), [
        '_config' => 'alipay-v3',
        'out_trade_no' => 'yansongda-2026',
        'out_request_no' => 'yansongda-2026-refund',
        'refund_amount' => '3.00',
        'refund_reason' => '正常退款',
    ]);

    expect($result->get('out_trade_no'))->toEqual('yansongda-2026');
    expect($result->get('refund_fee'))->toEqual('3.00');
    expect($result->get('fund_change'))->toEqual('Y');
});

it('退款错误响应', function () {
    // 支付宝返回非 2xx 错误体：无签名（有签才验放行）→ ResponsePlugin 抛业务异常，消息含 code 与 message
    $shortcut = new RefundShortcut();
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn(new Response(
        400,
        [],
        json_encode(['code' => 'INVALID_PARAMETER', 'message' => '参数错误'])
    ));
    Pay::set(HttpClientInterface::class, $http);

    Artful::artful($shortcut->getPlugins([]), [
        '_config' => 'alipay-v3',
        'out_trade_no' => 'yansongda-2026',
        'out_request_no' => 'yansongda-2026-refund',
        'refund_amount' => '-1.00',
    ]);
})->throws(InvalidResponseException::class, '支付宝返回状态码异常: [INVALID_PARAMETER] 参数错误，请检查参数是否错误', Exception::RESPONSE_CODE_WRONG);
