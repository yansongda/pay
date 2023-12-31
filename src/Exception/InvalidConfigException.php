<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidConfigException extends Exception
{
    public function __construct(int $code = self::CONFIG_ERROR, string $message = '配置异常', mixed $extra = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $extra, $previous);
    }
}
