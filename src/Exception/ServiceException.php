<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class ServiceException extends Exception
{
    /**
     * Bootstrap.
     */
    public function __construct(string $message = 'Service Error', int $code = self::SERVICE_ERROR, array $extra = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
