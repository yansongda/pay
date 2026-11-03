<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Bestpay;
use Yansongda\Pay\Traits\BestpayTrait;
use Yansongda\Pay\Tests\Support\Traits\BestpayProviderHelpers;

uses(BestpayTrait::class);
uses(BestpayProviderHelpers::class);

describe('shortcut', function () {
    it('不存在', function () {

        Pay::bestpay()->foo();
    })->throws(InvalidParamsException::class);

    it('不支持撤销', function () {

        Pay::bestpay()->cancel(['outTradeNo' => 'test']);
    })->throws(InvalidParamsException::class);
});

describe('success', function () {
    it('解析响应', function () {
        $response = Pay::bestpay()->success();

        expect($response)->toBeInstanceOf(ResponseInterface::class);
        expect((string) $response->getBody())->toEqual('{"resultCode":"SUCCESS","resultMsg":"OK"}');
        expect($response->getHeaderLine('Content-Type'))->toEqual('application/json');
    });
});

describe('sign', function () {
    it('待签名内容排序', function () {
        $content = self::getBestpaySignContent([
            'path' => '/pay/tradeCreate',
            'sign' => 'should-be-removed',
            'bizContent' => '{"a":1}',
            'commonParams' => '{"institutionType":"MERCHANT"}',
        ]);

        expect($content)->toEqual('bizContent={"a":1}&commonParams={"institutionType":"MERCHANT"}&path=/pay/tradeCreate');
    });

    it('签名与验签往返', function () {
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
});

describe('pay', function () {
    it('网页', function () {
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

    it('H5', function () {
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

    it('扫码', function () {
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
});

describe('order', function () {
    it('退款', function () {
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

    it('关单', function () {
        $this->mockResponse([
            'success' => true,
            'result' => ['outTradeNo' => 'ORDER001'],
        ]);

        $result = Pay::bestpay()->close([
            'outTradeNo' => 'ORDER001',
        ]);

        expect((bool) $result->get('success'))->toBeTrue();
    });
});

describe('query', function () {
    it('业务错误', function () {

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
    })->throws(InvalidResponseException::class);

    it('聚合动作', function () {
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

    it('动作非法', function () {

        Pay::bestpay()->query([
            'outTradeNo' => 'ORDER001',
            '_action' => 'not-exists',
        ]);
    })->throws(InvalidParamsException::class);
});

describe('provider', function () {
    it('Provider 类', function () {
        expect(Pay::bestpay())->toBeInstanceOf(Bestpay::class);
        expect('https://mapi.bestpay.com.cn/mapi')->toEqual(Bestpay::URL[PayMode::NORMAL->value]);
    });
});