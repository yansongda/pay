<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Yansongda\Pay\Plugin\Alipay\GeneralPayPlugin;

class QueryPlugin extends GeneralPayPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.trade.query';
    }
}
