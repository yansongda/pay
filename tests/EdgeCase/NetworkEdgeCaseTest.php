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

it('HTTP 超时', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new RequestException(
            'Connection timed out after 5 seconds',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    Pay::alipay()->query(['out_trade_no' => '123456']);
})->throws(InvalidResponseException::class);

it('HTTP 连接失败', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new ConnectException(
            'Connection refused',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    Pay::alipay()->query(['out_trade_no' => '123456']);
})->throws(InvalidResponseException::class);

it('空响应 body', function () {
    $response = new Response(200, [], '');

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);

    Pay::set(HttpClientInterface::class, $http);

    Pay::alipay()->query(['out_trade_no' => '123456']);
})->throws(InvalidResponseException::class);

it('响应 JSON 格式错误', function () {
    $response = new Response(200, [], 'not a valid json {{{');

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);

    Pay::set(HttpClientInterface::class, $http);

    Pay::alipay()->query(['out_trade_no' => '123456']);
})->throws(InvalidResponseException::class);

it('微信 HTTP 401 未授权', function () {
    $response = new Response(
        401,
        [],
        json_encode(['code' => 'SIGN_ERROR', 'message' => '签名错误'])
    );

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);

    Pay::set(HttpClientInterface::class, $http);

    Pay::wechat()->query(['out_trade_no' => '123456']);
})->throws(InvalidResponseException::class, null, Exception::RESPONSE_CODE_WRONG);

it('微信 HTTP 403 禁止访问', function () {
    $response = new Response(
        403,
        [],
        json_encode(['code' => 'NO_AUTH', 'message' => '无权限'])
    );

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);

    Pay::set(HttpClientInterface::class, $http);

    Pay::wechat()->query(['out_trade_no' => '123456']);
})->throws(InvalidResponseException::class, null, Exception::RESPONSE_CODE_WRONG);

it('DNS 解析失败', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new ConnectException(
            'DNS resolution failed for openapi.alipay.com',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    Pay::alipay()->query(['out_trade_no' => '123456']);
})->throws(InvalidResponseException::class);

it('SSL 证书错误', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new RequestException(
            'SSL certificate problem: unable to get local issuer certificate',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    Pay::alipay()->query(['out_trade_no' => '123456']);
})->throws(InvalidResponseException::class);

it('网络重置', function () {
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')
        ->andThrow(new ConnectException(
            'Connection reset by peer',
            new Request('POST', 'https://openapi.alipay.com/gateway.do')
        ));

    Pay::set(HttpClientInterface::class, $http);

    Pay::alipay()->query(['out_trade_no' => '123456']);
})->throws(InvalidResponseException::class);

afterEach(function () {
    Mockery::close();
Pay::clear();
});
