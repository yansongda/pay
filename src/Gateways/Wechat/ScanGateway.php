<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class ScanGateway extends Wechat
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
        return 'NATIVE';
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
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException("Missing Config -- [app_id]");
        }

        return $this->preOrder($config_biz)['code_url'];
    }
}
