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

        $logger = $this->getDefaultLogger();

        if (class_exists(\Monolog\Logger::class) && true === $config->get('logger.enable', false)) {
            $logger = new Logger(array_merge(
                ['identify' => 'yansongda.pay'], $config->get('logger', [])
            ));
        }

        Pay::set(LoggerInterface::class, $logger);
    }

    protected function getDefaultLogger(): \Psr\Log\LoggerInterface
    {
        return new class() implements \Psr\Log\LoggerInterface {
            /**
             * System is unusable.
             *
             * @param string $message
             */
            public function emergency($message, array $context = []): void
            {
            }

            /**
             * Action must be taken immediately.
             *
             * Example: Entire website down, database unavailable, etc. This should
             * trigger the SMS alerts and wake you up.
             *
             * @param string $message
             *
             * @return void
             */
            public function alert($message, array $context = [])
            {
            }

            /**
             * Critical conditions.
             *
             * Example: Application component unavailable, unexpected exception.
             *
             * @param string $message
             *
             * @return void
             */
            public function critical($message, array $context = [])
            {
            }

            /**
             * Runtime errors that do not require immediate action but should typically
             * be logged and monitored.
             *
             * @param string $message
             *
             * @return void
             */
            public function error($message, array $context = [])
            {
            }

            /**
             * Exceptional occurrences that are not errors.
             *
             * Example: Use of deprecated APIs, poor use of an API, undesirable things
             * that are not necessarily wrong.
             *
             * @param string $message
             *
             * @return void
             */
            public function warning($message, array $context = [])
            {
            }

            /**
             * Normal but significant events.
             *
             * @param string $message
             *
             * @return void
             */
            public function notice($message, array $context = [])
            {
            }

            /**
             * Interesting events.
             *
             * Example: User logs in, SQL logs.
             *
             * @param string $message
             *
             * @return void
             */
            public function info($message, array $context = [])
            {
            }

            /**
             * Detailed debug information.
             *
             * @param string $message
             *
             * @return void
             */
            public function debug($message, array $context = [])
            {
            }

            /**
             * Logs with an arbitrary level.
             *
             * @param mixed  $level
             * @param string $message
             *
             * @return void
             *
             * @throws \Psr\Log\InvalidArgumentException
             */
            public function log($level, $message, array $context = [])
            {
            }
        };
    }
}
