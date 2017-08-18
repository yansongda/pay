<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class MpGateway extends Wechat
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
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException("Missing Config -- [app_id]");
        }
        
        $payRequest = [
            "appId" => $this->user_config->get('app_id'),
            "timeStamp" => time(),    
            "nonceStr" => $this->createNonceStr(),   
            "package" => "prepay_id=" . $this->preOrder($config_biz)['prepay_id'],
            "signType" => "MD5",    
        ];
        $payRequest['paySign'] = $this->getSign($payRequest);

        return $payRequest;
    }
}
