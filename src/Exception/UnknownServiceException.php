<?php

namespace Yansongda\Pay\Exception;

use Throwable;

class UnknownServiceException extends ServiceException
{
    /**
     * Bootstrap.
     *
     * @param string $message
     * @param int    $code
     * @param array  $raw
     */
    public function __construct($message = 'Unknown Service Exception!', $code = self::UNKNOWN_SERVICE, $raw = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $raw, $previous);
    }
}
