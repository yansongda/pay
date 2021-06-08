<?php

namespace Yansongda\Pay\Tests\Provider;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Parser\NoHttpRequestParser;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\AbstractProvider;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class AbstractProviderTest extends TestCase
{
    protected function setUp(): void
    {
        $config = [
            'alipay' => [
                'default' => [
                    'app_public_cert_path' => __DIR__.'/../Stubs/cert/appCertPublicKey_2016082000295641.crt',
                    'alipay_public_cert_path' => __DIR__.'/../Stubs/cert/alipayCertPublicKey_RSA2.crt',
                    'alipay_root_cert_path' => __DIR__.'/../Stubs/cert/alipayRootCert.crt',
                ],
            ]
        ];
        Pay::config($config);
    }

    protected function tearDown(): void
    {
        Pay::clear();
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
        $response = new Response();
        $rocket = new Rocket();
        $rocket->setRadar(new Request('get', ''));

        $http = Mockery::mock(Client::class);
        $http->shouldReceive('sendRequest')->andReturn($response);

        Pay::set(HttpClientInterface::class, $http);

        $provider = new FooProviderStub();
        $result = $provider->ignite($rocket);

        self::assertSame($response, $result->getDestination());
    }
}

class FooProviderStub extends AbstractProvider
{
    public function find($order): Collection
    {
    }

    public function cancel($order): Collection
    {
    }

    public function close($order): Collection
    {
    }

    public function refund(array $order): Collection
    {
    }

    public function verify($contents = null, ?array $params = null): Collection
    {
    }

    public function success(): ResponseInterface
    {
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
