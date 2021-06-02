<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidParamsException extends Exception
{
    public function __construct(int $code = self::PARAMS_ERROR, string $message = 'Params Error', array $extra = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
