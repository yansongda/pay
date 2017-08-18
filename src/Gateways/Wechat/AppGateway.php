<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class AppGateway extends Wechat
{
    /**
     * 交易类型.
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-15
     * 
     * @return  string     [description]
     */
    protected function getTradeType()
    {
        return 'APP';
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
        if (is_null($this->user_config->get('appid'))) {
            throw new InvalidArgumentException("Missing Config -- [appid]");
        }

        $this->config['appid'] = $this->user_config->get('appid');

        $payRequest = [
            "appid" => $this->user_config->get('appid'),
            'partnerid' => $this->user_config->get('mch_id'),
            'prepayid' => $this->preOrder($config_biz)['prepay_id'],
            "timestamp" => time(),    
            "noncestr" => $this->createNonceStr(),   
            "package" => "Sign=WXPay", 
        ];
        $payRequest['sign'] = $this->getSign($payRequest);

        return $payRequest;
    }
}
