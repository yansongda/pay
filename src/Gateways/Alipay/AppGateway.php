<?php

namespace Yansongda\Pay\Gateways\Alipay;

class AppGateway extends Alipay
{
    /**
     * get method config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.app.pay';
    }

    /**
     * get productCode method.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getProductCode()
    {
        return 'QUICK_MSECURITY_PAY';
    }

    /**
     * pay a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config_biz
     *
     * @return string
     */
    public function pay(array $config_biz = [])
    {
        parent::pay($config_biz);


        /**
         * 支付宝支付报错 alin10146,原因支付宝不允许传空值，如return_url或notify_url有时为空，造成签名错误
         * @author kingofzihua
         * @link https://github.com/yansongda/pay/issues/119#ref-commit-bb4abeb
         */
        $this->config = array_filter($this->config, function ($value) {
            return $value !== '' && !is_null($value);
        });
        
        return http_build_query($this->config);
    }
}
