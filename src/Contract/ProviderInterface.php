<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

interface ProviderInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function pay(array $plugins, array $params): null|Collection|MessageInterface|Rocket;

    public function query(array $order): Collection|Rocket;

    public function cancel(array $order): Collection|Rocket;

    public function close(array $order): Collection|Rocket;

    public function refund(array $order): Collection|Rocket;

    public function callback(null|array|ServerRequestInterface $contents = null, ?array $params = null): Collection|Rocket;

    public function success(): ResponseInterface;
}
