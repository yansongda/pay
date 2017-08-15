<?php

namespace Yansongda\Pay\Gateways\Wechat;

/**
 * 微信支付 - H5 支付
 */
class WapGateway extends Wechat
{
    /**
     * [getTradeType description]
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
     * 支付
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

        return is_null($this->user_config->get('return_url')) ? $data['MWEB_URL'] : $data['MWEB_URL'] .
                        '&redirect_url=' . urlencode($this->config['return_url']);
    }
}
