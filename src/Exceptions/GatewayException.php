<?php

namespace Yansongda\Pay\Exceptions;

/**
 * GatewayException
 */
class GatewayException extends Exception
{
    /**
     * error raw data
     * 
     * @var array
     */
    public $raw = [];

    /**
     * [__construct description]
     * 
     * @author JasonYan <me@yansongda.cn>
     * 
     * @version 2017-07-28
     * 
     * @param   [type]     $message [description]
     * @param   [type]     $code    [description]
     */
    public function __construct($message, $code, $raw = [])
    {
        parent::__construct($message, intval($code));

        $this->raw = $raw;
    }
}
