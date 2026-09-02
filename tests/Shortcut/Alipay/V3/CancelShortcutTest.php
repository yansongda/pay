<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\V3;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
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
use Yansongda\Pay\Tests\TestCase;

class CancelShortcutTest extends TestCase
{
    protected CancelShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new CancelShortcut();
    }

    public function testNormal(): void
    {
        $plugins = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            CancelPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            VerifySignaturePlugin::class,
            ParserPlugin::class,
        ], $plugins);

        // 管道 post 阶段逆序执行：ResponsePlugin 必须位于 VerifySignaturePlugin 之前，
        // 使非 2xx 响应先经验签（有签才验）再抛业务异常
        self::assertGreaterThan(
            array_search(ResponsePlugin::class, $plugins, true),
            array_search(VerifySignaturePlugin::class, $plugins, true)
        );
    }

    public function testCancelHttp(): void
    {
        $body = json_encode([
            'trade_no' => '2026090222001400001234567890',
            'out_trade_no' => 'yansongda-2026',
            'action' => 'cancel',
            'retry_flag' => 'N',
        ], JSON_UNESCAPED_SLASHES);

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($this->makeSignedResponse(200, $body));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::alipay()->cancel(['_config' => 'alipay-v3', 'out_trade_no' => 'yansongda-2026']);

        self::assertEquals('yansongda-2026', $result->get('out_trade_no'));
        self::assertEquals('N', $result->get('retry_flag'));
    }

    /**
     * 生成带签名的模拟响应（签名密钥与 alipay-v3 测试租户支付宝公钥同属一对密钥）.
     */
    private function makeSignedResponse(int $status, string $body): Response
    {
        $timestamp = (string) (int) (microtime(true) * 1000);
        $nonce = 'yansongda-nonce';
        openssl_sign($timestamp."\n".$nonce."\n".$body."\n", $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipay-v3/app_secret_test.pem')), OPENSSL_ALGO_SHA256);

        return new Response($status, [
            'alipay-timestamp' => $timestamp,
            'alipay-nonce' => $nonce,
            'alipay-signature' => base64_encode($sign),
        ], $body);
    }
}
