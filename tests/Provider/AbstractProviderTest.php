<?php

namespace Yansongda\Pay\Tests\Provider;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\AbstractProvider;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AbstractProviderTest extends TestCase
{
    public function testVerifyObjectPlugin()
    {
        $plugin = [new FooPlugin()];

        $provider = new FooProviderStub();
        $result = $provider->pay($plugin, []);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testVerifyCallablePlugin()
    {
        $plugin = [function ($rocket, $next) {
            $rocket->setDirection(NoHttpRequestParser::class)
                ->setDestination(new Response());

            return $next($rocket);
        }];

        $provider = new FooProviderStub();
        $result = $provider->pay($plugin, []);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testVerifyNormalPlugin()
    {
        $plugin = [FooPlugin::class];

        $provider = new FooProviderStub();
        $result = $provider->pay($plugin, []);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testIgnite()
    {
        $response = new Response(200, [], 'yansongda/pay');
        $rocket = new Rocket();
        $rocket->setRadar(new Request('get', ''));

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        $provider = new FooProviderStub();
        $result = $provider->ignite($rocket);

        self::assertEquals('yansongda/pay', $result->getDestination()->getBody()->getContents());
    }

    public function testIgniteWrongHttpClient()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('get', ''));

        Pay::set(HttpClientInterface::class, new Collection());

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(InvalidConfigException::HTTP_CLIENT_CONFIG_ERROR);

        $provider = new FooProviderStub();
        $provider->ignite($rocket);
    }
}

class FooProviderStub extends AbstractProvider
{
    public function find($order): Collection
    {
        return new Collection();
    }

    public function cancel($order): Collection
    {
        return new Collection();
    }

    public function close($order): Collection
    {
        return new Collection();
    }

    public function refund(array $order): Collection
    {
        return new Collection();
    }

    public function callback($contents = null, ?array $params = null): Collection
    {
        return new Collection();
    }

    public function success(): ResponseInterface
    {
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return [];
    }
}

class FooPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->setDirection(NoHttpRequestParser::class)
            ->setDestination(new Response());

        return $next($rocket);
    }
}
