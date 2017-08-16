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
     * @return  [type]     [description]
     */
    protected function getPayMethod()
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
     * @return  [type]     [description]
     */
    protected function getPayProductCode()
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
     * @return  [type]                 [description]
     */
    public function pay(array $config_biz = [])
    {
        $config_biz['product_code'] = $this->getPayProductCode();

        $this->config['method'] = $this->getPayMethod();
        $this->config['biz_content'] = json_encode($config_biz, JSON_UNESCAPED_UNICODE);
        $this->config['sign'] = $this->getSign();

        return http_build_query($this->config);
    }
}
