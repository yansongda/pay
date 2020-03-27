<?php

namespace Yansongda\Pay\Exception;

use Throwable;

class ServiceNotFoundException extends Exception
{
    /**
     * Bootstrap.
     */
    public function __construct(
        $message = 'Service Not Found',
        $extra = [],
        $code = self::SERVICE_NOT_FOUND_EXCEPTION,
        Throwable $previous = null
    ) {
        parent::__construct($message, $extra, $code, $previous);
    }
}
