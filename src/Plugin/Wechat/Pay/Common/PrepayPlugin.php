<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Config;

class PrepayPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/pay/transactions/jsapi';
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        $payload = $this->getWechatId($config);

        if (!$rocket->getPayload()->has('notify_url')) {
            $payload['notify_url'] = $config->get('notify_url');
        }

        $rocket->mergePayload($payload);
    }

    protected function getWechatId(Config $config): array
    {
        return [
            'appid' => $config->get('mp_app_id', ''),
            'mchid' => $config->get('mch_id', ''),
        ];
    }
}
