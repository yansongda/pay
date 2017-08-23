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
    protected function getMethod()
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
    protected function getProductCode()
    {
        return 'QUICK_WAP_WAY';
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
