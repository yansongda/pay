<?php

namespace Yansongda\Pay\Exceptions;

class InvalidArgumentException extends Exception
{
    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string       $message
     * @param array|string $raw
     * @param int|string   $code
     */
    public function __construct($message, $raw = [], $code = 3)
    {
        parent::__construct($message, $raw, $code);
    }
}
