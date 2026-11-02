<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Douyin\V1\ObtainClientTokenPlugin;

beforeEach(function () {
    // 重置进程内 client_token 静态缓存（trait 静态属性按使用类隔离，反射需指向使用类）
    $property = new ReflectionProperty(ObtainClientTokenPlugin::class, 'clientTokens');
    $property->setValue(null, []);
});

it('外部 access token', function () {
    $plugin = new ObtainClientTokenPlugin();
    // params 注入 `_access_token` 优先，不应触发任何 HTTP 子调用
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->never();
    Pay::set(HttpClientInterface::class, $http);

    $rocket = new Rocket();
    $rocket->setParams(['_access_token' => 'external_token_123']);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_access_token'))->toEqual('external_token_123');

    Mockery::close();
});

it('子调用获取 token', function () {
    $plugin = new ObtainClientTokenPlugin();
    $capturedBody = '';
    $response = new Response(200, [], json_encode([
        'data' => [
            'access_token' => 'clt.9f2c8a1e',
            'expires_in' => 7200,
            'error_code' => 0,
            'description' => '',
        ],
        'message' => 'success',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->once()->andReturnUsing(function ($request) use (&$capturedBody, $response) {
        $capturedBody = (string) $request->getBody();

        return $response;
    });

    Pay::set(HttpClientInterface::class, $http);

    $rocket = new Rocket();
    $rocket->setParams([]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_access_token'))->toEqual('clt.9f2c8a1e');

    // 请求体只含三个鉴权字段
    $body = json_decode($capturedBody, true);
    expect(array_keys($body))->toBe(['grant_type', 'client_key', 'client_secret']);

    Mockery::close();
});

it('静态缓存避免二次子调用', function () {
    // 进程内静态缓存：同 app_id 第二次调用直接命中缓存，不再触发 HTTP 子调用
    $response = new Response(200, [], json_encode([
        'data' => [
            'access_token' => 'clt.9f2c8a1e',
            'expires_in' => 7200,
            'error_code' => 0,
            'description' => '',
        ],
        'message' => 'success',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->once()->andReturn($response);
    Pay::set(HttpClientInterface::class, $http);

    $plugin = new ObtainClientTokenPlugin();

    $first = $plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
    $second = $plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);

    expect($first->getPayload()->get('_access_token'))->toEqual('clt.9f2c8a1e');
    expect($second->getPayload()->get('_access_token'))->toEqual('clt.9f2c8a1e');

    // once() 验证：第二次调用命中缓存未再发请求
    Mockery::close();
});

it('返回 rocket 与业务参数不污染子调用', function () {
    $plugin = new ObtainClientTokenPlugin();
    // 回归 #1196 模式问题：外层 `_return_rocket` + 业务字段不应影响 token 子调用
    $capturedBody = '';
    $response = new Response(200, [], json_encode([
        'data' => [
            'access_token' => 'clt.fresh_token',
            'expires_in' => 7200,
            'error_code' => 0,
            'description' => '',
        ],
        'message' => 'success',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->once()->andReturnUsing(function ($request) use (&$capturedBody, $response) {
        $capturedBody = (string) $request->getBody();

        return $response;
    });

    Pay::set(HttpClientInterface::class, $http);

    $rocket = new Rocket();
    $rocket->setParams([
        '_return_rocket' => true,
        'out_order_no' => '20240101123456',
        'total_amount' => 1,
        'subject' => '测试商品',
    ]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_access_token'))->toEqual('clt.fresh_token');

    // 请求体只含三个鉴权字段，业务字段与 `_return_rocket` 均未混入
    $body = json_decode($capturedBody, true);
    expect(array_keys($body))->toBe(['grant_type', 'client_key', 'client_secret']);

    Mockery::close();
});
