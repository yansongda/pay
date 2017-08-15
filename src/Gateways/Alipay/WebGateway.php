<?php

namespace Yansongda\Pay\Gateways\Alipay;

/**
 * class WebGateway.
 */
class WebGateway extends Alipay
{
    /**
     * [getMethod description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-10
     *
     * @return  [type]     [description]
     */
    protected function getPayMethod()
    {
        return 'alipay.trade.page.pay';
    }

    /**
     * [getProductCode description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-10
     *
     * @return  [type]     [description]
     */
    protected function getPayProductCode()
    {
        return 'FAST_INSTANT_TRADE_PAY';
    }
}
