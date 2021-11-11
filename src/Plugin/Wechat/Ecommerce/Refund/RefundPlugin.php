<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class RefundPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/ecommerce/refunds/apply';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        $wechatId = [
            'sp_appid' => $config->get('mini_app_id', $config->get('mp_app_id', '')),
        ];

        if (!$rocket->getPayload()->has('notify_url')) {
            $wechatId['notify_url'] = $config->get('notify_url');
        }

        $rocket->mergePayload($wechatId);
    }
}
