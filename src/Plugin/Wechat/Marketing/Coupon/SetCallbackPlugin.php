<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class SetCallbackPlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        $rocket->mergePayload([
            'mchid' => $config->get('mch_id', ''),
        ]);
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'v3/marketing/favor/callbacks';
    }
}
