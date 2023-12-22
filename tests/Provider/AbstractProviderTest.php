<?php

namespace Yansongda\Pay\Tests\Provider;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Direction\CollectionDirection;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\AbstractProvider;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AbstractProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Pay::set(DirectionInterface::class, CollectionDirection::class);
        Pay::set(PackerInterface::class, JsonPacker::class);
    }

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
            $rocket->setDirection(NoHttpRequestDirection::class)
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

        self::assertEquals('yansongda/pay', (string) $result->getDestination()->getBody());
    }

    public function testIgnitePreRead()
    {
        $response = new Response(200, [], 'yansongda/pay');
        $response->getBody()->read(1);

        $rocket = new Rocket();
        $rocket->setRadar(new Request('get', ''));

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        $provider = new FooProviderStub();
        $result = $provider->ignite($rocket);

        self::assertEquals('yansongda/pay', (string) $result->getDestination()->getBody());
    }

    public function testIgniteWrongHttpClient()
    {
        $rocket = new Rocket();
        $rocket->setRadar(new Request('get', ''));

        Pay::set(HttpClientInterface::class, new Collection());

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_HTTP_CLIENT_INVALID);

        $provider = new FooProviderStub();
        $provider->ignite($rocket);
    }

    public function testNoCommonPlugins()
    {
        $provider = new Foo2ProviderStub();
        $result = $provider->call(FooShortcut::class, ['_no_common_plugins' => true]);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }
}

class FooProviderStub extends AbstractProvider
{
    public function query(array $order): Collection
    {
        return new Collection();
    }

    public function cancel(array $order): Collection
    {
        return new Collection();
    }

    public function close(array $order): Collection
    {
        return new Collection();
    }

    public function refund(array $order): Collection
    {
        return new Collection();
    }

    public function callback(array|ServerRequestInterface $contents = null, ?array $params = null): Collection
    {
        return new Collection();
    }

    public function success(): ResponseInterface
    {
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['code' => 'SUCCESS', 'message' => '成功']),
        );
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return [];
    }
}

class Foo2ProviderStub extends FooProviderStub
{
    public function mergeCommonPlugins(array $plugins): array
    {
        return [new BarPlugin()];
    }
}

class FooPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestination(new Response());

        return $next($rocket);
    }
}

class BarPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->setRadar(new Request('get', ''));

        $rocket = $next($rocket);

        $rocket->setDestination(new Collection(['name' => 'yansongda']));

        return $rocket;
    }
}

class FooShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [FooPlugin::class];
    }
}
