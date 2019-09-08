<?php

namespace Yansongda\Pay\Exception;

use Throwable;

class ServiceException extends Exception
{
    /**
     * Bootstrap.
     *
     * @param string          $message
     * @param int             $code
     * @param array           $raw
     * @param \Throwable|null $previous
     */
    public function __construct($message = 'Service Exception', $code = self::SERVICE_EXCEPTION, $raw = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $raw, $previous);
    }
}
