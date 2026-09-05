<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Shortcut\Alipay\V3;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Pay\CertManager;
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
use Yansongda\Pay\Tests\TestCase;

class RefundShortcutTest extends TestCase
{
    protected RefundShortcut $shortcut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcut = new RefundShortcut();
    }

    public function testNormal(): void
    {
        $plugins = $this->shortcut->getPlugins([]);

        self::assertEquals([
            StartPlugin::class,
            RefundPlugin::class,
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

    public function testRefundHttp(): void
    {
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

        $result = Pay::alipay()->refund([
            '_config' => 'alipay-v3',
            'out_trade_no' => 'yansongda-2026',
            'out_request_no' => 'yansongda-2026-refund',
            'refund_amount' => '3.00',
            'refund_reason' => '正常退款',
        ]);

        self::assertEquals('yansongda-2026', $result->get('out_trade_no'));
        self::assertEquals('3.00', $result->get('refund_fee'));
        self::assertEquals('Y', $result->get('fund_change'));
    }

    public function testRefundErrorResponse(): void
    {
        // 支付宝返回非 2xx 错误体：无签名（有签才验放行）→ ResponsePlugin 抛业务异常，消息含 code 与 message
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(
            400,
            [],
            json_encode(['code' => 'INVALID_PARAMETER', 'message' => '参数错误'])
        ));
        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);
        self::expectExceptionMessage('支付宝返回状态码异常: [INVALID_PARAMETER] 参数错误，请检查参数是否错误');

        Pay::alipay()->refund([
            '_config' => 'alipay-v3',
            'out_trade_no' => 'yansongda-2026',
            'out_request_no' => 'yansongda-2026-refund',
            'refund_amount' => '-1.00',
        ]);
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
            'alipay-sn' => CertManager::alipayGetAppCertSn(__DIR__.'/../../../Cert/alipay-v3/alipay_public_cert_test.crt'),
        ], $body);
    }
}
