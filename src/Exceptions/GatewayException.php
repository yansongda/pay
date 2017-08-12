<?php 

namespace Yansongda\Pay\Exceptions;

/**
* GatewayException
*/
class GatewayException extends Exception
{
    /**
     * [__construct description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @param   [type]     $message [description]
     * @param   [type]     $code    [description]
     */
    public function __construct($message, $code)
    {
        parent::__construct($message, intval($code));
    }
}
