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
     * @return  string     [description]
     */
    protected function getTradeType()
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
     * @return  array                  [description]
     */
    public function pay(array $config_biz = [])
    {
        $this->config = array_merge($this->config, $config_biz);
        $this->config['appid'] = $this->user_config->get('miniapp_id');

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
