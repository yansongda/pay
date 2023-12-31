<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidResponseException extends Exception
{
    public mixed $response;

    public function __construct(
        int $code = self::RESPONSE_ERROR,
        string $message = '响应异常',
        mixed $extra = null,
        ?Throwable $previous = null
    ) {
        $this->response = $extra;

        parent::__construct($message, $code, $extra, $previous);
    }
}
