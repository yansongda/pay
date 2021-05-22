<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class ServiceException extends Exception
{
    /**
     * Bootstrap.
     */
    public function __construct(string $message = 'Service Error', array $extra = [], int $code = self::SERVICE_EXCEPTION, Throwable $previous = null) {
        parent::__construct($message, $extra, $code, $previous);
    }
}
