<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Papay;

use Yansongda\Pay\Plugin\Wechat\GeneralV2Plugin;
use Yansongda\Pay\Rocket;

/**
 * 支付中签约.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter3_5.shtml
 */
class ContractOrderPlugin extends GeneralV2Plugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'pay/contractorder';
    }
}
