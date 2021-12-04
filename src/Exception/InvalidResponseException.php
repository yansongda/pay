<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidResponseException extends Exception
{
    /**
     * @var \Throwable|null
     */
    public $exception = null;

    /**
     * @var mixed
     */
    public $response = null;

    /**
     * Bootstrap.
     *
     * @param mixed $extra
     */
    public function __construct(
        int $code = self::RESPONSE_ERROR,
        string $message = 'Provider response Error',
        $extra = null,
        ?Throwable $exception = null,
        Throwable $previous = null)
    {
        $this->response = $extra;
        $this->exception = $exception;

        parent::__construct($message, $code, $extra, $previous);
    }
}
