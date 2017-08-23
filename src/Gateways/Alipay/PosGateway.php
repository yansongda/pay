<?php

namespace Yansongda\Pay\Gateways\Alipay;

class PosGateway extends Alipay
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
        return 'alipay.trade.pay';
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
        return 'FACE_TO_FACE_PAYMENT';
    }

    /**
     * 刷卡支付.
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-23
     * 
     * @param   array      $config_biz 订单信息
     * @param   string     $scene      支付场景，默认二维码
     * 
     * @return  array|boolean          [description]
     */
    public function pay($config_biz, $scene = 'bar_code')
    {
        $config_biz['scene'] = $scene;

        return $this->getResult($config_biz, $this->getMethod());
    }
}
