<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidCallbackException extends Exception
{
    public mixed $callback;

    public function __construct(
        int $code = self::CALLBACK_ERROR,
        string $message = 'Callback Invalid',
        mixed $extra = null,
        ?Throwable $previous = null
    ) {
        $this->callback = $extra;

        parent::__construct($message, $code, $extra, $previous);
    }
}
