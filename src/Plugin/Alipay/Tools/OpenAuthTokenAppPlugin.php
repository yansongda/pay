<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Tools;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class OpenAuthTokenAppPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.open.auth.token.app';
    }
}
