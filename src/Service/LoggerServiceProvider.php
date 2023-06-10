<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Yansongda\Pay\Contract\ConfigInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;

class LoggerServiceProvider implements ServiceProviderInterface
{
    protected array $config = [
        'enable' => false,
        'file' => null,
        'identify' => 'yansongda.pay',
        'level' => Logger::DEBUG,
        'type' => 'daily',
        'max_files' => 30,
        'formatter' => "%datetime% > %channel%.%level_name% > %message% %context% %extra%\n\n",
    ];

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function register(mixed $data = null): void
    {
        /* @var ConfigInterface $config */
        $config = Pay::get(ConfigInterface::class);

        $this->config = array_merge($this->config, $config->get('logger', []));

        if (class_exists(Logger::class) && (true === ($this->config['enable'] ?? false))) {
            $logger = new Logger($this->config['identify']);

            $logger->pushHandler($this->getDefaultHandler()
                ->setFormatter($this->getDefaultFormatter()));

            Pay::set(LoggerInterface::class, $logger);
        }
    }

    protected function getDefaultFormatter(): LineFormatter
    {
        return new LineFormatter(
            $this->config['formatter'],
            null,
            false,
            true
        );
    }

    protected function getDefaultHandler(): AbstractProcessingHandler
    {
        $file = $this->config['file'] ?? (sys_get_temp_dir().'/logs/'.$this->config['identify'].'.log');

        return match ($this->config['type']) {
            'single' => new StreamHandler($file, $this->config['level']),
            default => new RotatingFileHandler($file, $this->config['max_files'], $this->config['level']),
        };
    }
}
