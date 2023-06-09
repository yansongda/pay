<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class ServiceException extends Exception
{
    public function __construct(string $message = 'Service Error', int $code = self::SERVICE_ERROR, mixed $extra = null, Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
