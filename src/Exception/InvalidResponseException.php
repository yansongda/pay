<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidResponseException extends Exception
{
    public function __construct(int $code = self::RESPONSE_ERROR, string $message = 'Provider response Error', array $extra = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
