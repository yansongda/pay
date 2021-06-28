<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\App;

use Yansongda\Pay\Rocket;
use Yansongda\Supports\Config;

class PrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\PrepayPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/pay/transactions/app';
    }

    protected function getWechatId(Config $config): array
    {
        return [
            'appid' => $config->get('app_id', ''),
            'mchid' => $config->get('mch_id', ''),
        ];
    }
}
