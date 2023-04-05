<?php

namespace Yansongda\Pay\Plugin\Wechat\Papay;

use Yansongda\Pay\Plugin\Wechat\GeneralV2Plugin;
use Yansongda\Pay\Rocket;

class PayContractOrderPlugin extends GeneralV2Plugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'pay/contractorder';
    }
}
