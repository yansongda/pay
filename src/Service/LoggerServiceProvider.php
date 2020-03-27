<?php

namespace Yansongda\Pay\Service;

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
     * @throws \Yansongda\Pay\Exception\ContainerException
     */
    public function register(Pay $pay): void
    {
        $service = function () {
            /* @var \Yansongda\Supports\Config $config */
            $config = Pay::get('config');
            $logger = new class($config) extends Logger {
                /**
                 * @var \Yansongda\Supports\Config
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
                 */
                public function __call($method, $args): void
                {
                    if (false === $this->conf->get('log.enable', false)) {
                        return;
                    }

                    parent::__call($method, $args);
                }
            };

            $logger->setConfig($config->get('log'));

            return $logger;
        };

        $pay::set('logger', $service);
        $pay::set('log', $service);
    }
}
