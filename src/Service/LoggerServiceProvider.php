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

        if (!class_exists(\Monolog\Logger::class) || false === $config->get('logger.enable', false)) {
            return;
        }

        $logger = new class($config) extends Logger {
            public function __construct(Config $config)
            {
                parent::__construct($config->get('logger', []));
            }

            /**
             * @throws \Exception
             */
            public function __call(string $method, array $args): void
            {
                parent::__call($method, $args);
            }
        };

        Pay::set(LoggerInterface::class, $logger);
        Pay::set(\Psr\Log\LoggerInterface::class, $logger);
    }
}
