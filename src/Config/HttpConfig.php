<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Supports\Config as BaseConfig;

class HttpConfig extends BaseConfig
{
    public function getTimeout(): float
    {
        return $this->get('timeout', 5.0);
    }

    public function getConnectTimeout(): float
    {
        return $this->get('connect_timeout', 5.0);
    }
}
