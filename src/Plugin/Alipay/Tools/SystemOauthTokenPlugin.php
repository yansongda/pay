<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Tools;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class SystemOauthTokenPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.system.oauth.token';
    }
}
