<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Mini;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Config;

class PrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\PrepayPlugin
{
    protected function getWechatId(Config $config, Rocket $rocket): array
    {
        $wechatId = parent::getWechatId($config, $rocket);

        if (Pay::MODE_SERVICE == $config->get('mode') && empty($wechatId['sp_appid'])) {
            $wechatId['sp_appid'] = $config->get('mp_app_id', '');
        }

        return $wechatId;
    }

    protected function getConfigKey(array $params): string
    {
        return 'mini_app_id';
    }
}
