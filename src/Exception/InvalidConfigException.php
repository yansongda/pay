<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidConfigException extends Exception
{
    public function __construct(string $message = 'Config Error', array $extra = [], int $code = self::CONFIG_EXCEPTION, Throwable $previous = null) {
        parent::__construct($message, $extra, $code, $previous);
    }
}
