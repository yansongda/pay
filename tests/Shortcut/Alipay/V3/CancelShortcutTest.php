<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\V3;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Pay\CertManager;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\CancelPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\V3\CancelShortcut;

uses(CancelShortcutTestHelpers::class);

it('正常', function () {
    $shortcut = new CancelShortcut();
    $plugins = $shortcut->getPlugins([]);

    expect($plugins)->toEqual([
        StartPlugin::class,
        CancelPlugin::class,
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

it('HTTP 撤销', function () {
    $shortcut = new CancelShortcut();
    $body = json_encode([
        'trade_no' => '2026090222001400001234567890',
        'out_trade_no' => 'yansongda-2026',
        'action' => 'cancel',
        'retry_flag' => 'N',
    ], JSON_UNESCAPED_SLASHES);

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($this->makeSignedResponse(200, $body));
    Pay::set(HttpClientInterface::class, $http);

    $result = Artful::artful($shortcut->getPlugins([]), ['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026']);

    expect($result->get('out_trade_no'))->toEqual('yansongda-2026');
    expect($result->get('retry_flag'))->toEqual('N');
});

trait CancelShortcutTestHelpers
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
