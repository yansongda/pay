<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class PageRefundPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.trade.page.refund';
    }
}
