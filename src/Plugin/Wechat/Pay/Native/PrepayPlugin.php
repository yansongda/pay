<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Native;

use Yansongda\Pay\Rocket;

class PrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\PrepayPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return $this->isServicePartnerMode(get_wechat_config($rocket->getParams())) 
                ? 'v3/pay/partner/transactions/native' 
                : 'v3/pay/transactions/native';
    }


}
