<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Tools;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

/**
 * @see https://opendocs.alipay.com/isv/03l8ca
 */
class OpenAuthTokenAppQueryPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.open.auth.token.app.query';
    }
}
