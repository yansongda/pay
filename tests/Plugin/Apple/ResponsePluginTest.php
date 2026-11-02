<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Apple\ResponsePlugin;
use Yansongda\Supports\Pipeline;

it('成功响应透传', function () {
    // 与 Artful::artful() 同构的 Pipeline 手动组装，走 Artful::ignite() 发 HTTP（真实弹栈）
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->once()->andReturn(
        new Response(200, [], '{"status":"ok"}')
    );
    Pay::set(HttpClientInterface::class, $http);

    $rocket = (new Rocket())->setRadar(new Request('GET', 'https://api.storekit-sandbox.apple.com/inApps/v1/transactions/123'));

    $result = Artful::make(Pipeline::class)
        ->send($rocket)
        ->through([ResponsePlugin::class])
        ->via('assembly')
        ->then(static fn (Rocket $rocket) => Artful::ignite($rocket));

    expect($result->getDestinationOrigin()->getStatusCode())->toBe(200);

    Mockery::close();
});

it('HTTP 状态码错误抛出对应错误码', function () {
    // 异常抛出后后续代码不可达，与 Wechat Openapi ResponsePluginTest 一致不调用 Mockery::close()
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->once()->andReturn(
        new Response(500, [], '{"errorMessage":"Internal Server Error"}')
    );
    Pay::set(HttpClientInterface::class, $http);

    $rocket = (new Rocket())->setRadar(new Request('GET', 'https://api.storekit-sandbox.apple.com/inApps/v1/transactions/123'));

    Artful::make(Pipeline::class)
        ->send($rocket)
        ->through([ResponsePlugin::class])
        ->via('assembly')
        ->then(static fn (Rocket $rocket) => Artful::ignite($rocket));
})->throws(InvalidResponseException::class, null, Exception::RESPONSE_CODE_WRONG);

it('无 HTTP 请求方向时跳过', function () {
    $plugin = new ResponsePlugin();
    // 本地操作场景（NoHttpRequestDirection）：即使 destinationOrigin 为 500 也不校验
    $rocket = new Rocket();
    $rocket->setDirection(NoHttpRequestDirection::class)
        ->setDestinationOrigin(new Response(500, [], '{"errorMessage":"Internal Server Error"}'))
        ->setDestination(new \Yansongda\Supports\Collection(['errorMessage' => 'Internal Server Error']));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result)->toBe($rocket);
});

