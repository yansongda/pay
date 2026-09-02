<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\V3;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\AddRadarPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\PrecreatePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\ResponsePlugin;
use Yansongda\Pay\Plugin\Alipay\V3\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Alipay\V3\ScanShortcut;
use Yansongda\Pay\Tests\TestCase;

class ScanShortcutTest extends TestCase
{
    protected ScanShortcut $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ScanShortcut();
    }

    public function testDefault(): void
    {
        $plugins = $this->plugin->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            PrecreatePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $plugins);

        // post 阶段按数组逆序执行：`ResponsePlugin` 必须位于 `VerifySignaturePlugin` 之前（数组序更靠前），
        // 使 post 阶段先验签（200 强制/其余有签才验，防篡改错误体）再由 `ResponsePlugin` 抛业务异常
        self::assertLessThan(
            array_search(VerifySignaturePlugin::class, $plugins, true),
            array_search(ResponsePlugin::class, $plugins, true),
        );
    }

    public function testEndToEnd(): void
    {
        $responseData = [
            'out_trade_no' => 'v3scan1704093802',
            'qr_code' => 'https://qr.alipay.com/bax07651xvtprxfkmxyf00a9',
        ];
        $body = json_encode($responseData);
        $timestamp = (string) (int) (microtime(true) * 1000);
        $nonce = 'yansongda-nonce';

        openssl_sign(
            $timestamp."\n".$nonce."\n".$body."\n",
            $sign,
            openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipay-v3/app_secret_test.pem')),
            OPENSSL_ALGO_SHA256
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [
            'alipay-timestamp' => $timestamp,
            'alipay-nonce' => $nonce,
            'alipay-signature' => base64_encode($sign),
        ], $body));
        Pay::set(HttpClientInterface::class, $http);

        $result = Artful::artful($this->plugin->getPlugins([]), [
            '_config' => 'alipay-v3',
            'out_trade_no' => 'v3scan1704093802',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Scan',
            '_return_rocket' => true,
        ]);

        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('https://openapi.alipay.com/v3/alipay/trade/precreate', (string) $radar->getUri());

        $requestBody = json_decode((string) $radar->getBody(), true);
        self::assertSame('v3scan1704093802', $requestBody['out_trade_no']);
        self::assertSame('0.01', $requestBody['total_amount']);
        self::assertSame('yansongda 测试 - V3 Scan', $requestBody['subject']);

        self::assertEqualsCanonicalizing($responseData, $result->getDestination()->all());
    }

    public function testEndToEndCertMode(): void
    {
        $responseData = [
            'out_trade_no' => 'v3scancert1704093802',
            'qr_code' => 'https://qr.alipay.com/bax07651xvtprxfkmxyf00a9',
        ];
        $body = json_encode($responseData);
        $timestamp = (string) (int) (microtime(true) * 1000);
        $nonce = 'yansongda-nonce';

        openssl_sign(
            $timestamp."\n".$nonce."\n".$body."\n",
            $sign,
            openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipay-v3/app_secret_test.pem')),
            OPENSSL_ALGO_SHA256
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [
            'alipay-timestamp' => $timestamp,
            'alipay-nonce' => $nonce,
            'alipay-signature' => base64_encode($sign),
            'alipay-sn' => CertManager::alipayGetAppCertSn(__DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt'),
        ], $body));
        Pay::set(HttpClientInterface::class, $http);

        $result = Artful::artful($this->plugin->getPlugins([]), [
            '_config' => 'alipay-v3-cert',
            'out_trade_no' => 'v3scancert1704093802',
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - V3 Scan Cert',
            '_return_rocket' => true,
        ]);

        self::assertEquals('https://openapi.alipay.com/v3/alipay/trade/precreate', (string) $result->getRadar()->getUri());
        self::assertEqualsCanonicalizing($responseData, $result->getDestination()->all());
    }
}
