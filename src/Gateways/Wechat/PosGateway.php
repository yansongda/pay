<?php

namespace Yansongda\Pay\Gateways\Wechat;

/**
 * 微信支付 - 刷卡支付.
 */
class PosGateway extends Wechat
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

    public function pay(array $config_biz = [])
    {
        # code...
    }
}
