<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\H5;

use Yansongda\Pay\Rocket;

class PrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\PrepayPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return $this->isServicePartnerMode(get_wechat_config($rocket->getParams())) 
                ? 'v3/pay/partner/transactions/h5' 
                : 'v3/pay/transactions/h5';
    }
}
