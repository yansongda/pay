<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Jsapi;

use Yansongda\Pay\Rocket;
use Yansongda\Pay\Pay;

class InvokePrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getAppid(Rocket $rocket): string
    {
        $config = get_wechat_config($rocket->getParams());

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            return $rocket->getPayload()->get('sub_appid', $config->get('sub_mp_app_id', ''));
        }

        return $config->get('mp_app_id', '');
    }
}
