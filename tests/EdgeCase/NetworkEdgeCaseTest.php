<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

it('http timeout', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new RequestException(
            'Connection timed out after 5 seconds',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);

    Pay::alipay()->query(['out_trade_no' => '123456']);
});

it('http connection failed', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new ConnectException(
            'Connection refused',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);

    Pay::alipay()->query(['out_trade_no' => '123456']);
});

it('empty response body', function () {
    $response = new Response(200, [], '');

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);

    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);

    Pay::alipay()->query(['out_trade_no' => '123456']);
});

it('malformed json response', function () {
    $response = new Response(200, [], 'not a valid json {{{');

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);

    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);

    Pay::alipay()->query(['out_trade_no' => '123456']);
});

it('wechat http401 unauthorized', function () {
    $response = new Response(
        401,
        [],
        json_encode(['code' => 'SIGN_ERROR', 'message' => '签名错误'])
    );

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);

    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

    Pay::wechat()->query(['out_trade_no' => '123456']);
});

it('wechat http403 forbidden', function () {
    $response = new Response(
        403,
        [],
        json_encode(['code' => 'NO_AUTH', 'message' => '无权限'])
    );

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);

    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

    Pay::wechat()->query(['out_trade_no' => '123456']);
});

it('dns resolution failure', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new ConnectException(
            'DNS resolution failed for openapi.alipay.com',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);

    Pay::alipay()->query(['out_trade_no' => '123456']);
});

it('ssl certificate error', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new RequestException(
            'SSL certificate problem: unable to get local issuer certificate',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);

    Pay::alipay()->query(['out_trade_no' => '123456']);
});

it('network reset', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new ConnectException(
            'Connection reset by peer',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    $this->expectException(InvalidResponseException::class);

    Pay::alipay()->query(['out_trade_no' => '123456']);
});

afterEach(function () {
    Mockery::close();
Pay::clear();
});
