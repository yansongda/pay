<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

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

    protected function validateRequired(): void {}
}
