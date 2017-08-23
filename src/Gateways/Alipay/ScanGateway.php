<?php

namespace Yansongda\Pay\Gateways\Alipay;

class ScanGateway extends Alipay
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
        return 'alipay.trade.precreate';
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
        return '';
    }

    /**
     * 扫码支付.
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-23
     * 
     * @param   array      $config_biz 订单信息
     * 
     * @return  array|boolean          [description]
     */
    public function pay(array $config_biz = [])
    {
        return $this->getResult($config_biz, $this->getMethod());
    }
}
