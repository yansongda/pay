<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Supports\Config as BaseConfig;

class LoggerConfig extends BaseConfig
{
    public function isEnable(): bool
    {
        return $this->get('enable', false);
    }

    public function getFile(): ?string
    {
        return $this->get('file', './logs/pay.log');
    }

    public function getLevel(): string
    {
        return $this->get('level', 'info');
    }

    public function getType(): string
    {
        return $this->get('type', 'single');
    }

    public function getMaxFile(): int
    {
        return $this->get('max_file', 30);
    }
}
