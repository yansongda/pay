<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Stubs;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Contract\ProviderInterface;
use Yansongda\Supports\Collection;

/**
 * 最小可用 Provider 桩，用于验证 AbstractServiceProvider 的注册链路。
 */
class ConcreteProvider implements ProviderInterface
{
    public function pay(array $plugins, array $params): Collection|MessageInterface|Rocket|null
    {
        return null;
    }

    public function query(array $order): Collection|Rocket
    {
        return new Collection();
    }

    public function cancel(array $order): Collection|Rocket
    {
        return new Collection();
    }

    public function close(array $order): Collection|Rocket
    {
        return new Collection();
    }

    public function refund(array $order): Collection|Rocket
    {
        return new Collection();
    }

    public function callback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection|Rocket
    {
        return new Collection();
    }

    public function success(): ResponseInterface
    {
        return new Response();
    }
}
