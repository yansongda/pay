<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Config;
use Yansongda\Supports\Logger;

class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function register(Pay $pay, ?array $data = null): void
    {
        /* @var ConfigInterface $config */
        $config = Pay::get(ConfigInterface::class);

        $logger = new class($config) extends Logger {
            /**
             * @var array
             */
            private $conf;

            /**
             * Bootstrap.
             */
            public function __construct(Config $config)
            {
                $this->conf = $config->get('log', []);

                parent::__construct($this->conf);
            }

            /**
             * __call.
             *
             * @author yansongda <me@yansongda.cn>
             *
             * @throws \Exception
             */
            public function __call(string $method, array $args): void
            {
                if (false === $this->conf['enable'] ?? false) {
                    return;
                }

                parent::__call($method, $args);
            }
        };

        $pay::set(LoggerInterface::class, $logger);
    }
}
