<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Alipay\V3;

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Alipay\V3\VerifySignaturePlugin;
use Yansongda\Pay\Tests\TestCase;

class VerifySignaturePluginTest extends TestCase
{
    protected VerifySignaturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new VerifySignaturePlugin();
    }

    public function testShouldNotDoRequest(): void
    {
        $rocket = (new Rocket())->setDirection(NoHttpRequestDirection::class)->setDestinationOrigin(new Response());
        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        self::assertSame($rocket, $result);

        $rocket = (new Rocket())->setDestinationOrigin(null);
        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        self::assertSame($rocket, $result);
    }

    public function testNormalPublicKeyMode(): void
    {
        $body = json_encode(['code' => '10000', 'msg' => 'Success']);
        $response = $this->makeSignedResponse(200, $body);

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($rocket, $result);
    }

    public function testNormalCertMode(): void
    {
        $body = json_encode(['code' => '10000', 'msg' => 'Success']);
        $response = $this->makeSignedResponse(200, $body, 'alipay-v3-cert', CertManager::alipayGetAppCertSn(__DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt'));

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3-cert'])->setDestinationOrigin($response);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($rocket, $result);
    }

    public function testNon200WithSignVerifyOk(): void
    {
        // 非 200 有签：同样验签，通过后放行进入错误处理
        $body = json_encode(['code' => 'INVALID_PARAMETER', 'message' => '参数错误']);
        $response = $this->makeSignedResponse(400, $body);

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($rocket, $result);
    }

    public function testNon200WithoutSignPass(): void
    {
        // 非 200 无签：直接放行进入错误处理（不做时间戳校验，对齐官方「有签才验」）
        $response = new Response(400, [], json_encode(['code' => 'INVALID_PARAMETER', 'message' => '参数错误']));

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($rocket, $result);
    }

    public function testNon200WithoutSignButExpiredTimestampPass(): void
    {
        // 非 200 无签且时间戳过期：仍应放行（不能被时间戳异常拦截而无法进入业务错误处理）
        $response = new Response(400, ['alipay-timestamp' => '1735689600123', 'alipay-nonce' => 'yansongda-nonce'], '{}');

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($rocket, $result);
    }

    public function testForceVerifyWithoutSign(): void
    {
        // HTTP 200 强制验签：无签名也必须验签（空签抛异常）
        $response = new Response(200, ['alipay-timestamp' => (string) (int) (microtime(true) * 1000), 'alipay-nonce' => 'yansongda-nonce'], '{}');

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testInvalidSignature(): void
    {
        $response = new Response(
            200,
            ['alipay-timestamp' => (string) (int) (microtime(true) * 1000), 'alipay-nonce' => 'yansongda-nonce', 'alipay-signature' => 'invalid-sign'],
            '{}'
        );

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testTimestampExpired(): void
    {
        // 毫秒时间戳超过 ±300 秒（换算后）：抛 InvalidSignException
        $timestamp = '1735689600123';
        $nonce = 'yansongda-nonce';
        $body = '{}';
        $sign = $this->sign($timestamp."\n".$nonce."\n".$body."\n");

        $response = new Response(
            200,
            ['alipay-timestamp' => $timestamp, 'alipay-nonce' => $nonce, 'alipay-signature' => base64_encode($sign)],
            $body
        );

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3'])->setDestinationOrigin($response);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);
        self::expectExceptionMessage('签名异常: 支付宝 V3 时间戳已过期');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testCertModeSnMismatch(): void
    {
        $body = json_encode(['code' => '10000']);
        $response = $this->makeSignedResponse(200, $body, 'alipay-v3-cert', 'invalid-alipay-sn');

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3-cert'])->setDestinationOrigin($response);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);
        self::expectExceptionMessage('签名异常: 支付宝公钥证书已过期/不匹配，请重新下载最新支付宝公钥证书并替换');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testCertModeSnMissing(): void
    {
        // 有意偏差：证书模式下 alipay-sn 缺失一律抛异常（官方会回落缓存第一个公钥，此处更严格）
        $body = json_encode(['code' => '10000']);
        $response = $this->makeSignedResponse(200, $body, 'alipay-v3-cert');

        $rocket = (new Rocket())->setParams(['_config' => 'alipay-v3-cert'])->setDestinationOrigin($response);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);
        self::expectExceptionMessage('签名异常: 支付宝公钥证书已过期/不匹配，请重新下载最新支付宝公钥证书并替换');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    /**
     * 生成带签名的模拟响应（签名密钥与测试租户支付宝公钥/公钥证书同属一对密钥）.
     */
    private function makeSignedResponse(int $status, string $body, string $tenant = 'alipay-v3', string $alipaySn = ''): Response
    {
        $timestamp = (string) (int) (microtime(true) * 1000);
        $nonce = 'yansongda-nonce';
        $sign = $this->sign($timestamp."\n".$nonce."\n".$body."\n");

        $headers = ['alipay-timestamp' => $timestamp, 'alipay-nonce' => $nonce, 'alipay-signature' => base64_encode($sign)];

        if ('alipay-v3-cert' === $tenant) {
            $headers['alipay-sn'] = $alipaySn;
        }

        return new Response($status, $headers, $body);
    }

    private function sign(string $content): string
    {
        openssl_sign($content, $sign, openssl_pkey_get_private(file_get_contents(__DIR__.'/../../../Cert/alipay-v3/app_secret_test.pem')), OPENSSL_ALGO_SHA256);

        return $sign;
    }
}
