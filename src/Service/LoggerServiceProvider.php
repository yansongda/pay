<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Pay;
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

        $logger = new class() implements \Psr\Log\LoggerInterface {
            public function emergency($message, array $context = []): void
            {
            }

            public function alert($message, array $context = [])
            {
            }

            public function critical($message, array $context = [])
            {
            }

            public function error($message, array $context = [])
            {
            }

            public function warning($message, array $context = [])
            {
            }

            public function notice($message, array $context = [])
            {
            }

            public function info($message, array $context = [])
            {
            }

            public function debug($message, array $context = [])
            {
            }

            public function log($level, $message, array $context = [])
            {
            }
        };

        if (class_exists(\Monolog\Logger::class) && true === $config->get('logger.enable', false)) {
            $logger = new Logger(array_merge(
                ['identify' => 'yansongda.pay'], $config->get('logger', [])
            ));
        }

        Pay::set(LoggerInterface::class, $logger);
    }
}
