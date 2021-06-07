<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Ebpp;

use Yansongda\Pay\Plugin\Alipay\GeneralPayPlugin;

class PdeductBillPayStatusPlugin extends GeneralPayPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.ebpp.pdeduct.bill.pay.status';
    }
}
