<?php

namespace Yansongda\Pay\Exceptions;

class GatewayException extends Exception
{
    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string       $message
     * @param array|string $raw
     */
    public function __construct($message, $raw = [])
    {
        parent::__construct('ERROR_GATEWAY: '.$message, $raw, self::ERROR_GATEWAY);
    }
}
