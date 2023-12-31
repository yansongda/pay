<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class DecryptException extends Exception
{
    public function __construct(
        int $code = self::DECRYPT_ERROR,
        string $message = '加解密异常',
        mixed $extra = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $extra, $previous);
    }
}
