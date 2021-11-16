<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class ApplyPlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        throw new InvalidParamsException(Exception::NOT_IN_SERVICE_MODE);
    }

    protected function getPartnerUri(Rocket $rocket): string
    {
        return 'v3/ecommerce/refunds/apply';
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();

        $key = ($rocket->getParams()['_type'] ?? 'mp').'_app_id';
        if ('app_app_id' === $key) {
            $key = 'app_id';
        }

        $wechatId = [
            'sub_mchid' => $payload->get('sub_mchid', $config->get('sub_mch_id', '')),
            'sp_appid' => $payload->get('sp_appid', $config->get($key, '')),
        ];

        if (!$payload->has('notify_url')) {
            $wechatId['notify_url'] = $config->get('notify_url');
        }

        $rocket->mergePayload($wechatId);
    }
}
