<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class TransOrderQueryPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.fund.trans.order.query';
    }
}
