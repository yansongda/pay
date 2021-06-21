<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class AuthOrderUnfreezePlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.fund.auth.order.unfreeze';
    }
}
