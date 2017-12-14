<?php

namespace Yansongda\Pay\Exceptions;

class GatewayException extends Exception
{
    public $raw;

    public function __construct($message, $code, $raw = '')
    {
        parent::__construct($message, intval($code));

        $this->raw = $raw;
    }
}
