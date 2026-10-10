<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Apple;

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
use Yansongda\Pay\Plugin\Apple\ResponsePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Pipeline;

class ResponsePluginTest extends TestCase
{
    protected ResponsePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ResponsePlugin();
    }

    public function testSuccessResponsePassesThrough(): void
    {
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

        self::assertSame(200, $result->getDestinationOrigin()->getStatusCode());

        Mockery::close();
    }

    public function testHttpStatusCodeErrorThrowsCodeWrong(): void
    {
        // 异常抛出后后续代码不可达，与 Wechat Openapi ResponsePluginTest 一致不调用 Mockery::close()
        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->once()->andReturn(
            new Response(500, [], '{"errorMessage":"Internal Server Error"}')
        );
        Pay::set(HttpClientInterface::class, $http);

        $rocket = (new Rocket())->setRadar(new Request('GET', 'https://api.storekit-sandbox.apple.com/inApps/v1/transactions/123'));

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

        Artful::make(Pipeline::class)
            ->send($rocket)
            ->through([ResponsePlugin::class])
            ->via('assembly')
            ->then(static fn (Rocket $rocket) => Artful::ignite($rocket));
    }

    public function testNoHttpRequestDirectionSkips(): void
    {
        // 本地操作场景（NoHttpRequestDirection）：即使 destinationOrigin 为 500 也不校验
        $rocket = new Rocket();
        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestinationOrigin(new Response(500, [], '{"errorMessage":"Internal Server Error"}'))
            ->setDestination(new \Yansongda\Supports\Collection(['errorMessage' => 'Internal Server Error']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertSame($rocket, $result);
    }
}
