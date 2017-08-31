<?php

namespace Yansongda\Pay\Gateways\Alipay;

class PosGateway extends Alipay
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
        return 'alipay.trade.pay';
    }

    /**
     * get productCode config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getProductCode()
    {
        return 'FACE_TO_FACE_PAYMENT';
    }

    /**
     * pay a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array  $config_biz
     * @param string $scene
     *
     * @return array|bool
     */
    public function pay(array $config_biz = [], $scene = 'bar_code')
    {
        $config_biz['scene'] = $scene;

        return $this->getResult($config_biz, $this->getMethod());
    }
}
