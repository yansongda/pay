<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class ContainerNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    /**
     * Bootstrap.
     */
    public function __construct(string $message = 'Container Not Found', array $extra = [], int $code = self::CONTAINER_NOT_FOUND, Throwable $previous = null)
    {
        parent::__construct($message, $extra, $code, $previous);
    }
}
