<?php

namespace Yansongda\Pay\Plugin\Wechat\Papay;

use Yansongda\Pay\Plugin\Wechat\GeneralV2Plugin;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_5.shtml
 */
class PayContractOrderPlugin extends GeneralV2Plugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'pay/contractorder';
    }
}
