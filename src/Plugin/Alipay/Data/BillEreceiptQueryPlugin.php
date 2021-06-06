<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Data;

use Yansongda\Pay\Plugin\Alipay\GeneralPayPlugin;

class BillEreceiptQueryPlugin extends GeneralPayPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.data.bill.ereceipt.query';
    }
}
