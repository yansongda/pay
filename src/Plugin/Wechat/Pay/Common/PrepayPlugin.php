<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Config;

class PrepayPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/pay/transactions/jsapi';
    }

    protected function getPartnerUri(Rocket $rocket): string
    {
        return 'v3/pay/partner/transactions/jsapi';
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        $wechatId = $this->getWechatId($config, $rocket);

        if (!$rocket->getPayload()->has('notify_url')) {
            $wechatId['notify_url'] = $config->get('notify_url');
        }

        $rocket->mergePayload($wechatId);
    }

    protected function getWechatId(Config $config, Rocket $rocket): array
    {
        $payload = $rocket->getPayload();

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            return [
                'sp_appid' => $config->get('mp_app_id', ''),
                'sp_mchid' => $config->get('mch_id', ''),
                'sub_appid' => $payload->get('sub_appid', $config->get('sub_mp_app_id')),
                'sub_mchid' => $payload->get('sub_mchid', $config->get('sub_mch_id')),
            ];
        }

        return [
            'appid' => $config->get('mp_app_id', ''),
            'mchid' => $config->get('mch_id', ''),
        ];
    }
}
