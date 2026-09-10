<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Bestpay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\BestpayTrait;

class BestpayTest extends TestCase
{
    use BestpayTrait;

    public function testShortcutNotFound(): void
    {
        self::expectException(InvalidParamsException::class);

        Pay::bestpay()->foo();
    }

    public function testCancelNotSupported(): void
    {
        self::expectException(InvalidParamsException::class);

        Pay::bestpay()->cancel(['outTradeNo' => 'test']);
    }

    public function testSuccessBody(): void
    {
        $response = Pay::bestpay()->success();

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertEquals('{"resultCode":"SUCCESS","resultMsg":"OK"}', (string) $response->getBody());
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testSignContentSorted(): void
    {
        $content = self::getBestpaySignContent([
            'path' => '/pay/tradeCreate',
            'sign' => 'should-be-removed',
            'bizContent' => '{"a":1}',
            'commonParams' => '{"institutionType":"MERCHANT"}',
            'empty' => '',
            'null' => null,
        ]);

        self::assertEquals(
            'bizContent={"a":1}&commonParams={"institutionType":"MERCHANT"}&path=/pay/tradeCreate',
            $content
        );
    }

    public function testSignAndVerifyRoundTrip(): void
    {
        $bestpayConfig = new \Yansongda\Pay\Config\BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => __DIR__.'/../Cert/bestpay/bestpay.p12',
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => __DIR__.'/../Cert/bestpay/bestpay.cer',
        ]);

        $content = self::getBestpaySignContent([
            'path' => '/pay/tradeCreate',
            'commonParams' => '{"institutionType":"MERCHANT","institutionCode":"3178033925245778"}',
            'bizContent' => '{"merchantNo":"3178033925245778"}',
        ]);

        $sign = self::signBestpayContent($bestpayConfig, $content);

        self::assertNotEmpty($sign);
        self::assertNotFalse(base64_decode($sign, true));

        self::verifyBestpaySign($bestpayConfig, [
            'path' => '/pay/tradeCreate',
            'commonParams' => '{"institutionType":"MERCHANT","institutionCode":"3178033925245778"}',
            'bizContent' => '{"merchantNo":"3178033925245778"}',
            'sign' => $sign,
        ]);

        self::assertTrue(true);
    }

    public function testWebPay(): void
    {
        $body = [
            'success' => true,
            'errorCode' => null,
            'errorMsg' => null,
            'result' => [
                'outTradeNo' => 'ORDER001',
                'tradeNo' => 'TRADE001',
                'tradeStatus' => 'WAITFORPAY',
            ],
        ];
        $body['sign'] = $this->signResponseBody($body);

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($body)));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::bestpay()->web([
            'outTradeNo' => 'ORDER001',
            'tradeAmt' => '99',
            'subject' => '测试订单',
            'goodsInfo' => '测试商品',
            'merchantNo' => '3178033925245778',
            'operator' => '3178033925245778',
            'requestDate' => date('Y-m-d H:i:s'),
        ]);

        self::assertTrue((bool) $result->get('success'));
        self::assertEquals('ORDER001', $result->get('result.outTradeNo'));
        self::assertEquals('WAITFORPAY', $result->get('result.tradeStatus'));
    }

    public function testQueryBusinessError(): void
    {
        $this->expectException(InvalidResponseException::class);

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode([
            'success' => false,
            'errorCode' => 'API100001',
            'errorMsg' => '签名认证失败',
            'result' => null,
        ])));
        Pay::set(HttpClientInterface::class, $http);

        Pay::bestpay()->query([
            'outTradeNo' => 'ORDER001',
            'merchantNo' => '3178033925245778',
        ]);
    }

    public function testProviderClass(): void
    {
        self::assertInstanceOf(Bestpay::class, Pay::bestpay());
        self::assertEquals(Bestpay::URL[Pay::MODE_NORMAL], 'https://mapi.bestpay.com.cn/mapi');
    }

    /**
     * @param array<string, mixed> $body
     */
    private function signResponseBody(array $body): string
    {
        $config = new \Yansongda\Pay\Config\BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => __DIR__.'/../Cert/bestpay/bestpay.p12',
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => __DIR__.'/../Cert/bestpay/bestpay.cer',
        ]);

        return self::signBestpayContent($config, self::getBestpayResponseSignContent($body));
    }
}
