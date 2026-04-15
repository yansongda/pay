<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Bestpay\V1\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\ResponsePlugin;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\FooPluginStub;
use Yansongda\Pay\Tests\TestCase;

class BestpayTest extends TestCase
{
    public function testShortcutNotFound(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_SHORTCUT_INVALID);

        Pay::bestpay()->foo();
    }

    public function testMergeCommonPlugins(): void
    {
        Pay::config([]);
        $plugins = [FooPluginStub::class];

        self::assertEquals(array_merge(
            [StartPlugin::class],
            $plugins,
            [AddPayloadSignPlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class],
        ), Pay::bestpay()->mergeCommonPlugins($plugins));
    }

    public function testCancel(): void
    {
        self::expectException(InvalidParamsException::class);

        Pay::bestpay()->cancel(['tradeOrder' => 'test_order_001']);
    }

    public function testClose(): void
    {
        self::expectException(InvalidParamsException::class);

        Pay::bestpay()->close(['tradeOrder' => 'test_order_001']);
    }

    public function testCallbackWithArray(): void
    {
        $params = [
            'merchantNo' => 'bestpay_merchant_no',
            'platform' => 'HELIPAY',
            'returnCode' => '0000',
            'returnMsg' => '成功',
            'tradeOrder' => 'test_order_001',
            'totalAmount' => '100',
        ];

        $filtered = array_filter($params, fn ($v) => '' !== $v && null !== $v);
        ksort($filtered);
        $sign = strtolower(md5(http_build_query($filtered).'&key=bestpay_app_key_123456'));
        $params['sign'] = $sign;

        $result = Pay::bestpay()->callback($params);

        self::assertNotEmpty($result->all());
        self::assertEquals('test_order_001', $result->get('tradeOrder'));
    }

    public function testCallbackWithServerRequest(): void
    {
        $params = [
            'merchantNo' => 'bestpay_merchant_no',
            'platform' => 'HELIPAY',
            'returnCode' => '0000',
            'tradeOrder' => 'test_order_002',
        ];

        $filtered = array_filter($params, fn ($v) => '' !== $v && null !== $v);
        ksort($filtered);
        $sign = strtolower(md5(http_build_query($filtered).'&key=bestpay_app_key_123456'));
        $params['sign'] = $sign;

        $body = json_encode($params);
        $request = new ServerRequest('POST', 'https://example.com/bestpay/notify', ['Content-Type' => 'application/json'], $body);

        $result = Pay::bestpay()->callback($request);

        self::assertNotEmpty($result->all());
        self::assertEquals('test_order_002', $result->get('tradeOrder'));
    }

    public function testSuccess(): void
    {
        $result = Pay::bestpay()->success();

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertEquals(200, $result->getStatusCode());
        self::assertStringContainsString('0000', (string) $result->getBody());
    }

    public function testWeb(): void
    {
        $response = json_encode([
            'returnCode' => '0000',
            'returnMsg' => '成功',
            'sign' => 'mock_sign',
            'data' => [
                'tradeOrder' => 'test_order_003',
                'redirectUrl' => 'https://pay.bestpay.com.cn/redirect',
            ],
        ]);

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::bestpay()->web([
            'tradeOrder' => 'test_order_003',
            'productName' => '测试商品',
            'totalAmount' => '100',
        ]);

        self::assertArrayHasKey('returnCode', $result->all());
        self::assertEquals('0000', $result->get('returnCode'));
    }

    public function testScan(): void
    {
        $response = json_encode([
            'returnCode' => '0000',
            'returnMsg' => '成功',
            'sign' => 'mock_sign',
            'data' => [
                'tradeOrder' => 'test_order_004',
                'qrCode' => 'https://qr.bestpay.com.cn/code',
            ],
        ]);

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], $response));
        Pay::set(HttpClientInterface::class, $http);

        $result = Pay::bestpay()->scan([
            'tradeOrder' => 'test_order_004',
            'productName' => '测试商品',
            'totalAmount' => '100',
        ]);

        self::assertArrayHasKey('returnCode', $result->all());
        self::assertEquals('0000', $result->get('returnCode'));
    }
}
