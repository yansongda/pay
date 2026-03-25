<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V3\VerifySignaturePlugin;
use Yansongda\Pay\Tests\TestCase;

use function Yansongda\Pay\get_private_cert;
use function Yansongda\Pay\get_provider_config;

class VerifySignaturePluginTest extends TestCase
{
    protected VerifySignaturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new VerifySignaturePlugin();
    }

    public function testShouldNotDoRequest()
    {
        $rocket = new Rocket();
        $rocket->setDirection(NoHttpRequestDirection::class)->setDestinationOrigin(new Response());
        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        self::assertSame($rocket, $result);

        $rocket = new Rocket();
        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        self::assertSame($rocket, $result);
    }

    public function testNormal()
    {
        $body = '{"code":"10000","msg":"Success"}';
        $timestamp = '1711353600000';
        $nonce = 'test_nonce_1234567890';
        $content = $timestamp."\n".$nonce."\n".$body."\n";
        openssl_sign($content, $sign, get_private_cert(get_provider_config('alipay', ['_config' => 'v3_sign'])['app_secret_cert']), OPENSSL_ALGO_SHA256);

        $response = new Response(200, [
            'alipay-timestamp' => $timestamp,
            'alipay-nonce' => $nonce,
            'alipay-signature' => base64_encode($sign),
        ], $body);

        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'v3_sign'])->setDestinationOrigin($response);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertTrue(true);
    }
}
