<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\H5;

use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_3_1.shtml
 */
class PrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\PrepayPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/pay/transactions/h5';
    }

    protected function getPartnerUri(Rocket $rocket): string
    {
        return 'v3/pay/partner/transactions/h5';
    }
}
