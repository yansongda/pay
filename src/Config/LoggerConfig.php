<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Pay\Pay;

class LoggerConfig extends AbstractConfig
{
    private bool $enable = false;
    private string $file = './logs/pay.log';
    private string $level = 'info';
    private string $type = 'single';
    private int $max_file = 30;

    public function setEnable(bool $value): void
    {
        $this->enable = $value;
    }

    public function setFile(string $value): void
    {
        $this->file = $value;
    }

    public function setLevel(string $value): void
    {
        $this->level = $value;
    }

    public function setType(string $value): void
    {
        $this->type = $value;
    }

    public function setMaxFile(int $value): void
    {
        $this->max_file = $value;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function getEnable(): bool
    {
        return $this->enable;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMaxFile(): int
    {
        return $this->max_file;
    }

    public function getMode(): int
    {
        return Pay::MODE_NORMAL;
    }

    protected function validateRequired(): void {}
}
