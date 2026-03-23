<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

class HttpConfig implements ConfigInterface
{
    public function __construct(
        public float $timeout = 5.0,
        public float $connect_timeout = 5.0,
        public array $options = [],
    ) {
    }

    public function toArray(): array
    {
        return array_merge(
            [
                'timeout' => $this->timeout,
                'connect_timeout' => $this->connect_timeout,
            ],
            $this->options
        );
    }

    public static function fromArray(array $config): self
    {
        $timeout = $config['timeout'] ?? 5.0;
        $connect_timeout = $config['connect_timeout'] ?? 5.0;
        unset($config['timeout'], $config['connect_timeout']);

        return new self(
            timeout: $timeout,
            connect_timeout: $connect_timeout,
            options: $config,
        );
    }
}
