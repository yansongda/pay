<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Mini;

use Yansongda\Supports\Config;

class PrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\PrepayPlugin
{
    protected function getWechatId(Config $config): array
    {
        return [
            'appid' => $config->get('mini_app_id', ''),
            'mchid' => $config->get('mch_id', ''),
        ];
    }
}
