<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidParamsException extends Exception
{
    public function __construct(string $message = 'Params Error', int $code = self::PARAMS_ERROR, array $extra = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
