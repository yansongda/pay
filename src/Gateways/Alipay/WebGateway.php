<?php 

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\InvalidArgumentException;

/**
* 
*/
class WebGateway extends Alipay
{
    const WEB_METHOD = 'alipay.trade.page.pay';
    const WEB_PRODUCT_CODE = 'FAST_INSTANT_TRADE_PAY';
    
    public function pay()
    {
        # code...
    }
}