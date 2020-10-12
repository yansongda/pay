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
     * {@inheritdoc}
     */
    public function prepare(array $data): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerNotFoundException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function register(Pay $pay): void
    {
        /* @var ConfigInterface $config */
        $config = Pay::get(ConfigInterface::class);

        $service = function () use ($config) {
            $logger = new class($config) extends Logger {
                /**
                 * @var ConfigInterface
                 */
                private $conf;

                /**
                 * Bootstrap.
                 */
                public function __construct(Config $config)
                {
                    $this->conf = $config;
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
                    if (false === $this->conf->get('log.enable', true)) {
                        return;
                    }

                    parent::__call($method, $args);
                }
            };

            $logger->setConfig($config->get('log'));

            return $logger;
        };

        $pay::set(LoggerInterface::class, $service);
    }
}
