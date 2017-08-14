<?php 

namespace Yansongda\Pay\Gateways\Wechat;

/**
* 
*/
class JsGateway extends Wechat
{
    public function getTradeType()
    {
        return 'JSAPI';
    }
}
