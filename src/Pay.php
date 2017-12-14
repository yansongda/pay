<?php

namespace Yansongda\Pay;

use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

class Pay
{
    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

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
    public static function create($method, $params = [])
    {
        $gateway = __NAMESPACE__ . '\\Gateways\\' . Str::studly($method);

        if (class_exists($gateway)) {
            return $this->make($gateway, $params);
        }

        $this->config->get('log') ?? $this->registeLog($this->config->get('log'));

        throw new GatewayException("Gateway [{$method}] not exists", 1);
    }

    /**
     * Make a instance.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string $gateway
     * @param array $params
     *
     * @return GatewayApplicationInterface
     */
    protected function make($gateway, $params)
    {
        $this->config = new Config($params);

        $app = new $gateway($this->config);

        if ($app instanceof GatewayApplicationInterface) {
            return $app;
        }

        throw new GatewayException("Gateway [$gateway] must be a instance of GatewayApplicationInterface", 2);
    }

    protected function registeLog($config)
    {
        $handler = new StreamHandler(sys_get_temp_dir() . '/logs/yansongda.pay.log');
        $handler->setFormatter(new LineFormatter("%datetime% > %level_name% > %message% %context% %extra%\n\n"));

        $logger = new Logger('yansongda.pay');
        $logger->pushHandler($handler);

        return $logger;
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
        return self::create($method, $params);
    }
}
