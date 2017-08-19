<?php

namespace Yansongda\Pay\Gateways\Alipay;

/**
 * WapGateway.
 */
class WapGateway extends Alipay
{
    /**
     * [getMethod description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-10
     *
     * @return  string     [description]
     */
    protected function getPayMethod()
    {
        return 'alipay.trade.wap.pay';
    }

    /**
     * [getProductCode description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-10
     *
     * @return  string     [description]
     */
    protected function getPayProductCode()
    {
        return 'QUICK_WAP_WAY';
    }
}
