<?php

namespace Yansongda\Pay;

use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Supports\Str;

class Pay
{
    /**
     * Make gateway.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array $params
     *
     * @return Yansongda\Pay\Contracts\GatewayApplicationInterface
     */
    public static function make($method, $params = [])
    {
        $app = __NAMESPACE__ . '\\Gateways\\' . Str::studly($method) . '\\Application';

        if (class_exists($app)) {
            return new $app($params);
        }

        throw new GatewayException("Gateway [{$method}] not exists", 1);
    }

    /**
     * Magic static call
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array $params
     *
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        return self::make($method, $params);
    }
}
