<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class RefundPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/refund/domestic/refunds';
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();

        if (empty($payload->get('notify_url'))) {
            $merge['notify_url'] = $config->get('notify_url', '');
        }

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            $merge['sub_mchid'] = $payload->get('sub_mchid', $config->get('sub_mch_id'));
        }

        $rocket->mergePayload($merge ?? []);
    }
}
