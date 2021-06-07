<?php

declare(strict_types=1);

namespace Yansongda\Pay\Exception;

use Throwable;

class InvalidResponseException extends Exception
{
    /**
     * @var array
     */
    public $response;

    public function __construct(int $code = self::RESPONSE_ERROR, string $message = 'Provider response Error', array $extra = [], Throwable $previous = null)
    {
        $this->response = $extra;

        parent::__construct($message, $code, $extra, $previous);
    }
}
