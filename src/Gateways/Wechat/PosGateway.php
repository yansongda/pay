<?php

namespace Yansongda\Pay\Gateways\Wechat;

/**
 * 微信支付 - 刷卡支付.
 */
class PosGateway extends Wechat
{
    public function getTradeType()
    {
        return 'JSAPI';
    }
}
