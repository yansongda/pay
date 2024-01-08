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
    public function pay(array $plugins, array $params): null|Collection|MessageInterface|Rocket;

    public function query(array $order): Collection|Rocket;

    public function cancel(array $order): Collection|Rocket;

    public function close(array $order): Collection|Rocket;

    public function refund(array $order): Collection|Rocket;

    public function callback(null|array|ServerRequestInterface $contents = null, ?array $params = null): Collection|Rocket;

    public function success(): ResponseInterface;
}
