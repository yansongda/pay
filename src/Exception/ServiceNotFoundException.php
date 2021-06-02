<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class ServiceNotFoundException extends Exception implements NotFoundExceptionInterface
{
    /**
     * Bootstrap.
     */
    public function __construct(string $message = 'Service Not Found', int $code = self::SERVICE_NOT_FOUND_ERROR, array $extra = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
