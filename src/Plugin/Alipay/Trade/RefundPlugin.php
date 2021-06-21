<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class RefundPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.trade.refund';
    }
}
