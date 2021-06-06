<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Yansongda\Pay\Plugin\Alipay\GeneralPayPlugin;

class TransOrderQueryPlugin extends GeneralPayPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.fund.trans.order.query';
    }
}
