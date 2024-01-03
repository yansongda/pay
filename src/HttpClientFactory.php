<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use GuzzleHttp\Client;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;

class HttpClientFactory implements Contract\HttpClientFactoryInterface
{
    public function __construct(private ContainerInterface $container) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws NotFoundExceptionInterface
     */
    public function create(?array $options = []): ClientInterface
    {
        if ($this->container->has(HttpClientInterface::class)) {
            if (($http = $this->container->get(HttpClientInterface::class)) instanceof ClientInterface) {
                return $http;
            }

            throw new InvalidConfigException(Exception::CONFIG_HTTP_CLIENT_INVALID, '配置异常: `HttpClient` 不符合 PSR 规范，可能你需要安装 `GuzzleHttp:^7`');
        }

        if (!class_exists(Client::class)) {
            throw new InvalidConfigException(Exception::CONFIG_HTTP_CLIENT_INVALID, '配置异常: 没有可用的 `HttpClient`，可能你需要安装 `GuzzleHttp:^7`');
        }

        return new Client($options);
    }
}
