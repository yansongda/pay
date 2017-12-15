<?php

namespace Yansongda\Pay;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Log;
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
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = new Config($config);
    }

    /**
     * Make gateway.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     *
     * @return GatewayApplicationInterface
     */
    protected function create($method)
    {
        !$this->config->has('log') ?: $this->registeLog();

        $gateway = __NAMESPACE__ . '\\Gateways\\' . Str::studly($method);

        if (class_exists($gateway)) {
            return self::make($gateway);
        }

        throw new GatewayException("Gateway [{$method}] not exists", 1);
    }

    /**
     * Make a instance.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string $gateway
     *
     * @return GatewayApplicationInterface
     */
    protected function make($gateway)
    {
        $app = new $gateway($this->config);

        if ($app instanceof GatewayApplicationInterface) {
            return $app;
        }

        throw new GatewayException("Gateway [$gateway] must be a instance of GatewayApplicationInterface", 2);
    }

    /**
     * Registe log service.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function registeLog()
    {
        $handler = new StreamHandler(
            $this->config->get('log.file', sys_get_temp_dir() . '/logs/yansongda.pay.log'),
            $this->config->get('log.level', Logger::WARNING)
        );
        $handler->setFormatter(new LineFormatter("%datetime% > %level_name% > %message% %context% %extra%\n\n"));

        $logger = new Logger('yansongda.pay');
        $logger->pushHandler($handler);

        Log::setLogger($logger);
    }

    /**
     * Magic static call
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array $params
     *
     * @return GatewayApplicationInterface
     */
    public static function __callStatic($method, $params)
    {
        $app = new static(...$params);

        return $app->create($method);
    }
}
