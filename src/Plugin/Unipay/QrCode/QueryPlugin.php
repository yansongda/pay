<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\QrCode;

use function Yansongda\Pay\get_unipay_config;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

/**
 * @see https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=792&apiservId=468&version=V2.2&bussType=0
 */
class QueryPlugin extends \Yansongda\Pay\Plugin\Unipay\OnlineGateway\QueryPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getUri(Rocket $rocket): string
    {
        $config = get_unipay_config($rocket->getParams());

        if (Pay::MODE_SANDBOX === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            return 'https://101.231.204.80:5000/gateway/api/backTransReq.do';
        }

        return parent::getUri($rocket);
    }
}
