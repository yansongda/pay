<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\V3;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Pay\CertManager;
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

uses(RefundShortcutTestHelpers::class);

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
    $shortcut = new RefundShortcut();
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn(new Response(
        400,
        [],
        json_encode(['code' => 'INVALID_PARAMETER', 'message' => '参数错误'])
    ));
    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);
    $this->expectExceptionMessage('支付宝返回状态码异常: [INVALID_PARAMETER] 参数错误，请检查参数是否错误');

    Artful::artful($shortcut->getPlugins([]), [
        '_config' => 'alipay-v3',
        'out_trade_no' => 'yansongda-2026',
        'out_request_no' => 'yansongda-2026-refund',
        'refund_amount' => '-1.00',
    ]);
});

trait RefundShortcutTestHelpers
{
    private function makeSignedResponse(int $status, string $body): Response
    {
        $timestamp = (string) (int) (microtime(true) * 1000);
        $nonce = 'yansongda-nonce';
        openssl_sign($timestamp."\n".$nonce."\n".$body."\n", $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipay-v3/app_secret_test.pem')), OPENSSL_ALGO_SHA256);
    
        return new Response($status, [
            'alipay-timestamp' => $timestamp,
            'alipay-nonce' => $nonce,
            'alipay-signature' => base64_encode($sign),
            'alipay-sn' => CertManager::alipayGetAppCertSn(__DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt'),
        ], $body);
    }
}
