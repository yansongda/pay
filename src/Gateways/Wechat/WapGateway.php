<?php 

namespace Yansongda\Pay\Gateways\Wechat;

/**
* 微信支付 - H5 支付
*/
class WapGateway extends Wechat
{
    public function getTradeType()
    {
        return 'MWEB';
    }
}
