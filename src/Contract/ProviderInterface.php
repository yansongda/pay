<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Supports\Collection;

interface ProviderInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function pay(array $plugins, array $params): null|array|Collection|MessageInterface;

    public function query(array $order): array|Collection;

    public function cancel(array $order): null|array|Collection;

    public function close(array|string $order): null|array|Collection;

    public function refund(array $order): array|Collection;

    public function callback(null|array|ServerRequestInterface $contents = null, ?array $params = null): Collection;

    public function success(): ResponseInterface;
}
