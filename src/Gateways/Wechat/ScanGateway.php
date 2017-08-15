<?php

namespace Yansongda\Pay\Gateways\Wechat;

/**
 * 微信支付 - 扫码支付.
 */
class ScanGateway extends Wechat
{
    /**
     * [getTradeType description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-15
     *
     * @return  [type]     [description]
     */
    public function getTradeType()
    {
        return 'NATIVE ';
    }

    /**
     * 对外支付，采用 「模式二」 进行支付.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-15
     *
     * @param   array      $config_biz [description]
     *
     * @return  string                 微信支付扫码 URL
     */
    public function pay(array $config_biz = [])
    {
        $this->config = array_merge($this->config, $config_biz);

        $data = $this->preOrder();

        return $data['code_url'];
    }
}
