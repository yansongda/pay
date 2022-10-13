<?php

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

/**
 * @see https://opendocs.alipay.com/open/02pj6l?ref=api
 */
class OrderSettleQueryPlugin extends GeneralPlugin
{
    public function getMethod(): string
    {
        return 'alipay.trade.order.settle.query';
    }
}