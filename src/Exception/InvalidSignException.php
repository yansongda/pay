<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidSignException extends Exception
{
    public mixed $callback;

    public function __construct(
        int $code = self::SIGN_ERROR,
        string $message = '签名异常',
        mixed $extra = null,
        ?Throwable $previous = null
    ) {
        $this->callback = $extra;

        parent::__construct($message, $code, $extra, $previous);
    }
}
