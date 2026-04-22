<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\EdgeCase;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;

class NetworkEdgeCaseTest extends TestCase
{
    public function testHttpTimeout(): void
    {
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')
            ->andThrow(new RequestException(
                'Connection timed out after 5 seconds',
                new Request('POST', 'https://openapi.alipay.com/gateway.do')
            ));

        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);

        Pay::alipay()->query(['out_trade_no' => '123456']);
    }

    public function testHttpConnectionFailed(): void
    {
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')
            ->andThrow(new ConnectException(
                'Connection refused',
                new Request('POST', 'https://openapi.alipay.com/gateway.do')
            ));

        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);

        Pay::alipay()->query(['out_trade_no' => '123456']);
    }

    public function testEmptyResponseBody(): void
    {
        $response = new Response(200, [], '');

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);

        Pay::alipay()->query(['out_trade_no' => '123456']);
    }

    public function testMalformedJsonResponse(): void
    {
        $response = new Response(200, [], 'not a valid json {{{');

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);

        Pay::alipay()->query(['out_trade_no' => '123456']);
    }

    public function testWechatHttp401Unauthorized(): void
    {
        $response = new Response(
            401,
            [],
            json_encode(['code' => 'SIGN_ERROR', 'message' => '签名错误'])
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        Pay::wechat()->query(['out_trade_no' => '123456']);
    }

    public function testWechatHttp403Forbidden(): void
    {
        $response = new Response(
            403,
            [],
            json_encode(['code' => 'NO_AUTH', 'message' => '无权限'])
        );

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        Pay::wechat()->query(['out_trade_no' => '123456']);
    }

    public function testDnsResolutionFailure(): void
    {
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')
            ->andThrow(new ConnectException(
                'DNS resolution failed for openapi.alipay.com',
                new Request('POST', 'https://openapi.alipay.com/gateway.do')
            ));

        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);

        Pay::alipay()->query(['out_trade_no' => '123456']);
    }

    public function testSslCertificateError(): void
    {
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')
            ->andThrow(new RequestException(
                'SSL certificate problem: unable to get local issuer certificate',
                new Request('POST', 'https://openapi.alipay.com/gateway.do')
            ));

        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);

        Pay::alipay()->query(['out_trade_no' => '123456']);
    }

    public function testNetworkReset(): void
    {
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')
            ->andThrow(new ConnectException(
                'Connection reset by peer',
                new Request('POST', 'https://openapi.alipay.com/gateway.do')
            ));

        Pay::set(HttpClientInterface::class, $http);

        self::expectException(InvalidResponseException::class);

        Pay::alipay()->query(['out_trade_no' => '123456']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Pay::clear();
    }
}