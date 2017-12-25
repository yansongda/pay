<?php

namespace Yansongda\Pay\Exceptions;

class InvalidConfigException extends Exception
{
    /**
     * Raw error info.
     *
     * @var array|string
     */
    public $raw;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string       $message
     * @param int|string   $code
     * @param array|string $raw
     */
    public function __construct($message, $code, $raw = '')
    {
        parent::__construct($message, intval($code));

        $this->raw = $raw;
    }
}
