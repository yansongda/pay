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
use Yansongda\Pay\Plugin\Wechat\Openapi\ResponsePlugin;
use Yansongda\Supports\Pipeline;
use Yansongda\Supports\Collection;

it('不应发起请求', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDirection(NoHttpRequestDirection::class)->setDestinationOrigin(new Response());
    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    expect($result)->toBe($rocket);

    $rocket = new Rocket();
    $rocket->setDestinationOrigin(null);
    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    expect($result)->toBe($rocket);
});

it('errcode 为 0 透传', function () {
    $plugin = new ResponsePlugin();
    $response = new Response(
        200,
        [],
        json_encode(['errcode' => 0, 'errmsg' => 'ok', 'data' => ['balance' => 100]], JSON_UNESCAPED_UNICODE),
    );

    $rocket = new Rocket();
    $rocket->setDestinationOrigin($response);
    $rocket->setDestination(new Collection(['errcode' => 0, 'errmsg' => 'ok', 'data' => ['balance' => 100]]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result)->toBe($rocket);
    expect($result->getDestination()->get('errcode'))->toBe(0);
});

it('errcode 非 0 抛出异常', function () {
    $plugin = new ResponsePlugin();
    $response = new Response(
        200,
        [],
        json_encode(['errcode' => 43001, 'errmsg' => 'invalid credential, access_token is invalid or not expired'], JSON_UNESCAPED_UNICODE),
    );

    $rocket = new Rocket();
    $rocket->setDestinationOrigin($response);
    $rocket->setDestination(new Collection(['errcode' => 43001, 'errmsg' => 'invalid credential, access_token is invalid or not expired']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidResponseException::class, '微信开放接口返回业务异常: invalid credential, access_token is invalid or not expired', Exception::RESPONSE_BUSINESS_CODE_WRONG);

it('无 destination 原始请求时返回 rocket', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result)->toBe($rocket);
});

it('HTTP 状态码错误抛出对应错误码', function () {
    $plugin = new ResponsePlugin();
    $response = new Response(
        500,
        [],
        json_encode(['errcode' => -1, 'errmsg' => 'system error'], JSON_UNESCAPED_UNICODE),
    );

    $rocket = new Rocket();
    $rocket->setDestinationOrigin($response);
    $rocket->setDestination(new Collection(['errcode' => -1, 'errmsg' => 'system error']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidResponseException::class, '微信开放接口返回状态码异常，请检查参数是否错误', Exception::RESPONSE_CODE_WRONG);

it('状态码 200 但带 errcode 抛出业务码', function () {
    $plugin = new ResponsePlugin();
    $response = new Response(
        200,
        [],
        json_encode(['errcode' => 43001, 'errmsg' => 'require POST'], JSON_UNESCAPED_UNICODE),
    );

    $rocket = new Rocket();
    $rocket->setDestinationOrigin($response);
    $rocket->setDestination(new Collection(['errcode' => 43001, 'errmsg' => 'require POST']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidResponseException::class, '微信开放接口返回业务异常: require POST', Exception::RESPONSE_BUSINESS_CODE_WRONG);

it('管道 HTTP 错误抛出对应错误码', function () {
    // 依赖 artful 契约（防 artful 升级静默失效）：
    // 1. Rocket 默认 direction 为 DirectionInterface::class，should_do_http_request() 返回 true
    //    （vendor/yansongda/artful/src/Rocket.php:35 + Functions.php:18-21）；
    // 2. HttpClientFactory::create() 无 _http 选项时优先返回容器内 HttpClientInterface
    //    （vendor/yansongda/artful/src/HttpClientFactory.php:34-38）。
    // 3. Artful::artful() 空 payload 场景下 Rocket::getRadar() 恒为 null（radar 仅由 AddRadarPlugin 设置，
    //    而 AddRadarPlugin 依赖 payload `_url`），ignite 内 sendRequest(null) 会 TypeError。故此处用与
    //    Artful::artful() 同构的 supports Pipeline 手动组装：预置 radar，then 与 Artful.php:267 完全一致
    //    走 Artful::ignite() 发 HTTP（真实弹栈：ignite → 合并插件 HTTP 检查先抛，errcode 检查不执行）。
    //    无需 ParserPlugin：HTTP 500 场景 destination 无需 unpack。
    // 不调用 Mockery::close()（异常抛出后后续代码不可达，与 WechatTest 一致）。
    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->once()->andReturn(
        new Response(500, [], json_encode(['errcode' => -1, 'errmsg' => 'system error'], JSON_UNESCAPED_UNICODE))
    );
    Pay::set(HttpClientInterface::class, $http);

    $rocket = (new Rocket())->setRadar(new Request('POST', 'https://api.weixin.qq.com/sns/jscode2session'));

    Artful::make(Pipeline::class)
        ->send($rocket)
        ->through([ResponsePlugin::class])
        ->via('assembly')
        ->then(static fn (Rocket $rocket) => Artful::ignite($rocket));
})->throws(InvalidResponseException::class, '微信开放接口返回状态码异常，请检查参数是否错误', Exception::RESPONSE_CODE_WRONG);

