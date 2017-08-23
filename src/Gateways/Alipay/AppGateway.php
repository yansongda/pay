<?php

namespace Yansongda\Pay\Gateways\Alipay;

/**
 * AppGateway.
 */
class AppGateway extends Alipay
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
        return 'alipay.trade.app.pay';
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
        return 'QUICK_MSECURITY_PAY';
    }

    /**
     * [pay description].
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-16
     * 
     * @param   array      $config_biz 业务参数
     * 
     * @return  string                 [description]
     */
    public function pay(array $config_biz = [])
    {
        parent::pay($config_biz);

        return http_build_query($this->config);
    }
}
