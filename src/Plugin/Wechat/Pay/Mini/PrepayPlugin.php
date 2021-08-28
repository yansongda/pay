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
        if (Pay::MODE_SERVICE == $config->get('mode')) {
            return [
                'sp_appid' => $config->get('app_id', ''),
                'sp_mchid' => $config->get('mch_id', ''),
                'sub_appid' => $rocket->getParams()['sub_appid'] ?? $config->get('sub_mini_app_id'),
                'sub_mchid' => $rocket->getParams()['sub_mchid'] ?? $config->get('sub_mch_id'),
            ];
        }

        return [
            'appid' => $config->get('mini_app_id', ''),
            'mchid' => $config->get('mch_id', ''),
        ];
    }
}
