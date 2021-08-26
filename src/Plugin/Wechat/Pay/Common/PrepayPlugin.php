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

        $payload = $this->getWechatId($config, $rocket);

        if (!$rocket->getPayload()->has('notify_url')) {
            $payload['notify_url'] = $config->get('notify_url');
        }

        $rocket->mergePayload($payload);
    }

    protected function getWechatId(Config $config, Rocket $rocket): array
    {
        if (Pay::MODE_SERVICE == $config->get('mode')) {
            return [
                'sp_appid' => $config->get('mp_app_id', ''),
                'sp_mchid' => $config->get('mch_id', ''),
                'sub_appid' => $config->get('sub_mp_appid') ?: ($rocket->getParams()['sub_appid'] ?? ''),
                'sub_mchid' => $config->get('sub_mchid') ?: ($rocket->getParams()['sub_mchid'] ?? ''),
            ];
        }

        return [
            'appid' => $config->get('mp_app_id', ''),
            'mchid' => $config->get('mch_id', ''),
        ];
    }
}
