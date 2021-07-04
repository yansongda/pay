<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Open;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class AuthTokenAppQueryPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.open.auth.token.app.query';
    }
}
