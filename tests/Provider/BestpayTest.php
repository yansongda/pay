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
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Bestpay;
use Yansongda\Pay\Traits\BestpayTrait;

uses(BestpayTrait::class);
uses(BestpayTestHelpers::class);

it('shortcut not found', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::bestpay()->foo();
});

it('cancel not supported', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::bestpay()->cancel(['outTradeNo' => 'test']);
});

it('success body', function () {
    $response = Pay::bestpay()->success();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect((string) $response->getBody())->toEqual('{"resultCode":"SUCCESS","resultMsg":"OK"}');
    expect($response->getHeaderLine('Content-Type'))->toEqual('application/json');
});

it('sign content sorted', function () {
    $content = self::getBestpaySignContent([
        'path' => '/pay/tradeCreate',
        'sign' => 'should-be-removed',
        'bizContent' => '{"a":1}',
        'commonParams' => '{"institutionType":"MERCHANT"}',
    ]);

    expect($content)->toEqual('bizContent={"a":1}&commonParams={"institutionType":"MERCHANT"}&path=/pay/tradeCreate');
});

it('sign and verify round trip', function () {
    $bestpayConfig = $this->defaultConfig();

    $content = self::getBestpaySignContent([
        'path' => '/pay/tradeCreate',
        'commonParams' => '{"institutionType":"MERCHANT","institutionCode":"3178033925245778"}',
        'bizContent' => '{"merchantNo":"3178033925245778"}',
    ]);

    $sign = self::signBestpayContent($bestpayConfig, $content);

    expect($sign)->not->toBeEmpty();
    expect(base64_decode($sign, true))->not->toBeFalse();

    self::verifyBestpaySign($bestpayConfig, [
        'path' => '/pay/tradeCreate',
        'commonParams' => '{"institutionType":"MERCHANT","institutionCode":"3178033925245778"}',
        'bizContent' => '{"merchantNo":"3178033925245778"}',
        'sign' => $sign,
    ]);

    expect(true)->toBeTrue();
});

it('web pay', function () {
    $this->mockResponse([
        'success' => true,
        'errorCode' => null,
        'errorMsg' => null,
        'result' => [
            'merchantNo' => '3178033925245778',
            'outTradeNo' => 'ORDER001',
            'tradeNo' => 'TRADE001',
            'tradeStatus' => 'WAITFORPAY',
        ],
    ]);

    // 不传 merchantNo，验证 StartPlugin 从配置注入
    $result = Pay::bestpay()->web([
        'outTradeNo' => 'ORDER001',
        'tradeAmt' => '99',
        'subject' => '测试订单',
        'goodsInfo' => '测试商品',
        'requestDate' => date('Y-m-d H:i:s'),
    ]);

    expect((bool) $result->get('success'))->toBeTrue();
    expect($result->get('result.outTradeNo'))->toEqual('ORDER001');
    expect($result->get('result.tradeStatus'))->toEqual('WAITFORPAY');
});

it('h5 pay', function () {
    $this->mockResponse([
        'success' => true,
        'result' => ['outTradeNo' => 'ORDER002', 'tradeStatus' => 'WAITFORPAY'],
    ]);

    $result = Pay::bestpay()->h5([
        'outTradeNo' => 'ORDER002',
        'tradeAmt' => '99',
        'subject' => '测试订单',
    ]);

    expect((bool) $result->get('success'))->toBeTrue();
    expect($result->get('result.outTradeNo'))->toEqual('ORDER002');
});

it('scan pay', function () {
    $this->mockResponse([
        'success' => true,
        'result' => ['outTradeNo' => 'ORDER003', 'codeUrl' => 'https://qr.bestpay.com.cn/xxx'],
    ]);

    $result = Pay::bestpay()->scan([
        'outTradeNo' => 'ORDER003',
        'tradeAmt' => '99',
        'subject' => '测试订单',
    ]);

    expect((bool) $result->get('success'))->toBeTrue();
    expect($result->get('result.outTradeNo'))->toEqual('ORDER003');
});

it('refund', function () {
    $this->mockResponse([
        'success' => true,
        'result' => ['outRefundNo' => 'REFUND001', 'refundStatus' => 'SUCCESS'],
    ]);

    $result = Pay::bestpay()->refund([
        'outTradeNo' => 'ORDER001',
        'outRequestNo' => 'REFUND001',
        'refundAmt' => '1',
        'requestDate' => date('Y-m-d H:i:s'),
    ]);

    expect((bool) $result->get('success'))->toBeTrue();
    expect($result->get('result.outRefundNo'))->toEqual('REFUND001');
});

it('close', function () {
    $this->mockResponse([
        'success' => true,
        'result' => ['outTradeNo' => 'ORDER001'],
    ]);

    $result = Pay::bestpay()->close([
        'outTradeNo' => 'ORDER001',
    ]);

    expect((bool) $result->get('success'))->toBeTrue();
});

it('query business error', function () {
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
    ]);
});

it('query aggregate action', function () {
    $this->mockResponseWithUriCheck(
        'path=%2Faggregate%2Faggregatepay%2FtradeQuery',
        [
            'success' => true,
            'result' => ['outTradeNo' => 'ORDER001', 'tradeStatus' => 'SUCCESS'],
        ]
    );

    $result = Pay::bestpay()->query([
        'outTradeNo' => 'ORDER001',
        '_action' => 'aggregate',
    ]);

    expect((bool) $result->get('success'))->toBeTrue();
    expect($result->get('result.tradeStatus'))->toEqual('SUCCESS');
});

it('query invalid action', function () {
    $this->expectException(InvalidParamsException::class);

    Pay::bestpay()->query([
        'outTradeNo' => 'ORDER001',
        '_action' => 'not-exists',
    ]);
});

it('provider class', function () {
    expect(Pay::bestpay())->toBeInstanceOf(Bestpay::class);
    expect('https://mapi.bestpay.com.cn/mapi')->toEqual(Bestpay::URL[PayMode::NORMAL->value]);
});

trait BestpayTestHelpers
{
    private function mockResponse(array $body): void
    {
        $body['sign'] = $this->signResponseBody($body);
    
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn(new Response(200, [], json_encode($body)));
        Pay::set(HttpClientInterface::class, $http);
    }
    
    private function mockResponseWithUriCheck(string $expectedBodyFragment, array $body): void
    {
        $body['sign'] = $this->signResponseBody($body);
    
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturnUsing(function ($request) use ($body, $expectedBodyFragment) {
            self::assertStringContainsString($expectedBodyFragment, (string) $request->getBody());
    
            return new Response(200, [], json_encode($body));
        });
        Pay::set(HttpClientInterface::class, $http);
    }
    
    private function signResponseBody(array $body): string
    {
        return self::signBestpayContent($this->defaultConfig(), self::getBestpaySignContent($body));
    }
    
    private function defaultConfig(): BestpayConfig
    {
        return new BestpayConfig([
            'merchant_no' => '3178033925245778',
            'institution_code' => '3178033925245778',
            'mch_secret_cert_path' => __DIR__.'/../Cert/bestpay/bestpay.p12',
            'mch_secret_cert_password' => 'test123456',
            'bestpay_public_cert_path' => __DIR__.'/../Cert/bestpay/bestpay.cer',
        ]);
    }
}
