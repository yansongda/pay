<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class TransCommonQueryPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.fund.trans.common.query';
    }
}
