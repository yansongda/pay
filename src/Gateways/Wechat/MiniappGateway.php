<?php

namespace Yansongda\Pay\Gateways\Wechat;

/**
 * 微信支付 - 扫码支付.
 */
class MiniappGateway extends Wechat
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
        return 'JSAPI';
    }

    /**
     * 对外支付.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-15
     *
     * @param   array      $config_biz [description]
     *
     * @return  [type]                 [description]
     */
    public function pay(array $config_biz = [])
    {
        $this->config = array_merge($this->config, $config_biz);
        $this->config['appid'] = $this->user_config->get('miniapp_id');
        $this->config['total_fee'] = intval($this->config['total_fee'] * 100);

        $payRequest = [
            "appId" => $this->user_config->get('miniapp_id'),
            "timeStamp" => time(),    
            "nonceStr" => $this->createNonceStr(),   
            "package" => "prepay_id=" . $this->preOrder()['prepay_id'],
            "signType" => "MD5", 
        ];
        $payRequest['paySign'] = $this->getSign($payRequest);

        return $payRequest;
    }
}
