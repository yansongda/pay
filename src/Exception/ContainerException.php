<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Psr\Container\ContainerExceptionInterface;
use Throwable;

class ContainerException extends Exception implements ContainerExceptionInterface
{
    /**
     * Bootstrap.
     */
    public function __construct(string $message = '', array $extra = [], int $code = self::CONTAINER_ERROR, Throwable $previous = null)
    {
        parent::__construct($message, $extra, $code, $previous);
    }
}
