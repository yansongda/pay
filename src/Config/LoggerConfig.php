<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

class LoggerConfig implements ConfigInterface
{
    public function __construct(
        public bool $enable = false,
        public string $file = './logs/pay.log',
        public string $level = 'info',
        public string $type = 'single',
        public int $max_file = 30,
    ) {
    }

    public function toArray(): array
    {
        return [
            'enable' => $this->enable,
            'file' => $this->file,
            'level' => $this->level,
            'type' => $this->type,
            'max_file' => $this->max_file,
        ];
    }

    public static function fromArray(array $config): self
    {
        return new self(
            enable: $config['enable'] ?? false,
            file: $config['file'] ?? './logs/pay.log',
            level: $config['level'] ?? 'info',
            type: $config['type'] ?? 'single',
            max_file: $config['max_file'] ?? 30,
        );
    }
}
