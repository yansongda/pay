<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

interface ProviderInterface
{
    /**
     * @param array<class-string>  $plugins
     * @param array<string, mixed> $params
     */
    public function pay(array $plugins, array $params): Collection|MessageInterface|Rocket|null;

    /**
     * @param array<string, mixed> $order
     */
    public function query(array $order): Collection|Rocket;

    /**
     * @param array<string, mixed> $order
     */
    public function cancel(array $order): Collection|Rocket;

    /**
     * @param array<string, mixed> $order
     */
    public function close(array $order): Collection|Rocket;

    /**
     * @param array<string, mixed> $order
     */
    public function refund(array $order): Collection|Rocket;

    /**
     * @param null|array<string, mixed>|ServerRequestInterface $contents
     * @param null|array<string, mixed>                        $params
     */
    public function callback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection|Rocket;

    public function success(): ResponseInterface;
}
