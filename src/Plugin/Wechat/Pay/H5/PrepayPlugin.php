<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\H5;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Config;

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

    protected function getWechatId(Config $config, Rocket $rocket): array
    {
        $payload = $rocket->getPayload();

        $key = ($rocket->getParams()['_type'] ?? 'mp').'_app_id';
        if ('app_app_id' === $key) {
            $key = 'app_id';
        }

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            return [
                'sp_appid' => $config->get($key, ''),
                'sp_mchid' => $config->get('mch_id', ''),
                'sub_appid' => $payload->get('sub_appid', $config->get('sub_'.$key)),
                'sub_mchid' => $payload->get('sub_mchid', $config->get('sub_mch_id')),
            ];
        }

        return [
            'appid' => $config->get($key, ''),
            'mchid' => $config->get('mch_id', ''),
        ];
    }
}
