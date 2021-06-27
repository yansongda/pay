<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\App;

use Yansongda\Pay\Rocket;

class InvokePrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayPlugin
{
    protected function getAppid(Rocket $rocket): string
    {
        $config = get_wechat_config($rocket->getParams());

        return $config->get('app_id', '');
    }
}
