<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Pay\Pay;

class HttpConfig extends AbstractConfig
{
    private float $timeout = 5.0;
    private float $connectTimeout = 5.0;

    public function setTimeout(float $value): void
    {
        $this->timeout = $value;
    }

    public function setConnectTimeout(float $value): void
    {
        $this->connectTimeout = $value;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getConnectTimeout(): float
    {
        return $this->connectTimeout;
    }

    public function getMode(): int
    {
        return Pay::MODE_NORMAL;
    }

    protected function validateRequired(): void {}
}
