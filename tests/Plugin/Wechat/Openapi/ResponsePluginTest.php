<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Openapi;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Openapi\ResponsePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Pipeline;
use Yansongda\Supports\Collection;

class ResponsePluginTest extends TestCase
{
    protected ResponsePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ResponsePlugin();
    }

    public function testShouldNotDoRequest()
    {
        $rocket = new Rocket();
        $rocket->setDirection(NoHttpRequestDirection::class)->setDestinationOrigin(new Response());
        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        self::assertSame($rocket, $result);

        $rocket = new Rocket();
        $rocket->setDestinationOrigin(null);
        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        self::assertSame($rocket, $result);
    }

    public function testErrcodeZeroPassesThrough()
    {
        $response = new Response(
            200,
            [],
            json_encode(['errcode' => 0, 'errmsg' => 'ok', 'data' => ['balance' => 100]], JSON_UNESCAPED_UNICODE),
        );

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($response);
        $rocket->setDestination(new Collection(['errcode' => 0, 'errmsg' => 'ok', 'data' => ['balance' => 100]]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
        self::assertSame(0, $result->getDestination()->get('errcode'));
    }

    public function testErrcodeNonZeroThrowsException()
    {
        $response = new Response(
            200,
            [],
            json_encode(['errcode' => 43001, 'errmsg' => 'invalid credential, access_token is invalid or not expired'], JSON_UNESCAPED_UNICODE),
        );

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($response);
        $rocket->setDestination(new Collection(['errcode' => 43001, 'errmsg' => 'invalid credential, access_token is invalid or not expired']));

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);
        $this->expectExceptionMessage('微信开放接口返回业务异常: invalid credential, access_token is invalid or not expired');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNoDestinationOriginReturnsRocket()
    {
        $rocket = new Rocket();

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($rocket, $result);
    }

    public function testHttpStatusCodeErrorThrowsCodeWrong()
    {
        $response = new Response(
            500,
            [],
            json_encode(['errcode' => -1, 'errmsg' => 'system error'], JSON_UNESCAPED_UNICODE),
        );

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($response);
        $rocket->setDestination(new Collection(['errcode' => -1, 'errmsg' => 'system error']));

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);
        $this->expectExceptionMessage('微信开放接口返回状态码异常，请检查参数是否错误');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testHttpStatusCodeOkWithErrcodeThrowsBusinessCode()
    {
        $response = new Response(
            200,
            [],
            json_encode(['errcode' => 43001, 'errmsg' => 'require POST'], JSON_UNESCAPED_UNICODE),
        );

        $rocket = new Rocket();
        $rocket->setDestinationOrigin($response);
        $rocket->setDestination(new Collection(['errcode' => 43001, 'errmsg' => 'require POST']));

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);
        $this->expectExceptionMessage('微信开放接口返回业务异常: require POST');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testPipelineHttpErrorThrowsCodeWrong()
    {
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

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);
        $this->expectExceptionMessage('微信开放接口返回状态码异常，请检查参数是否错误');

        Artful::make(Pipeline::class)
            ->send($rocket)
            ->through([ResponsePlugin::class])
            ->via('assembly')
            ->then(static fn (Rocket $rocket) => Artful::ignite($rocket));
    }
}
