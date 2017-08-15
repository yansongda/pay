<?php 

namespace Yansongda\Pay\Gateways\Wechat;

/**
* 微信支付 - 扫码支付
*/
class ScanGateway extends Wechat
{
    public function getTradeType()
    {
        return 'JSAPI';
    }
}
