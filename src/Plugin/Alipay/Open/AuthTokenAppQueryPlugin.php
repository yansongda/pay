<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Open;

use Yansongda\Pay\Plugin\Alipay\GeneralPayPlugin;

class AuthTokenAppQueryPlugin extends GeneralPayPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.open.auth.token.app.query';
    }
}
