<?php

namespace Yansongda\Pay\Gateways\Wechat;

/**
 * 微信支付 - H5 支付.
 */
class WapGateway extends Wechat
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
        return 'MWEB';
    }

    /**
     * 支付.
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

        $data = $this->preOrder();

        return is_null($this->user_config->get('return_url')) ? $data['mweb_url'] : $data['mweb_url'] . 
                        '&redirect_url=' . urlencode($this->user_config->get('return_url'));
    }
}
