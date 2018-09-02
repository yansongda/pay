<?php

namespace Yansongda\Pay\Exceptions;

class Exception extends \Exception
{
    /**
     * Raw error info.
     *
     * @var array
     */
    public $raw;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string       $message
     * @param array|string $raw
     * @param int|string   $code
     */
    public function __construct($message, $raw = [], $code = 9999)
    {
        $this->raw = is_array($raw) ? $raw : [$raw];

        parent::__construct($message, intval($code));
    }
}
