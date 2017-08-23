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
     * @return  string     [description]
     */
    protected function getMethod()
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
     * @return  string     [description]
     */
    protected function getProductCode()
    {
        return 'FAST_INSTANT_TRADE_PAY';
    }

    /**
     * 对外支付.
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-23
     * 
     * @param   array      $config_biz [description]
     * 
     * @return  string                 [description]
     */
    public function pay(array $config_biz = [])
    {
        parent::pay($config_biz);

        return $this->buildPayHtml();
    }
}
