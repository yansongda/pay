<?php

namespace Yansongda\Pay;

class Pay
{
    public function make($method, $params)
    {
        
    }

    public static function __callStatic($method, $params)
    {
        return self::make($method, $params);
    }
}
