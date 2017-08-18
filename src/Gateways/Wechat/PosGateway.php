<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class PosGateway extends Wechat
{
    /**
     * 刷卡支付 API.
     * 
     * @var string
     */
    protected $gateway = 'https://api.mch.weixin.qq.com/pay/micropay';

    /**
     * [getTradeType description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-15
     *
     * @return  [type]     [description]
     */
    protected function getTradeType()
    {
        return 'MICROPAY';
    }

    /**
     * [pay description].
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-18
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

        unset($this->config['trade_type']);
        unset($this->config['notify_url']);

        return $this->preOrder($config_biz);
    }
}
