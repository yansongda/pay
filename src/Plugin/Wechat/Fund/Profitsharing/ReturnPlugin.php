<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class ReturnPlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            $rocket->mergePayload([
                'sub_mchid' => $rocket->getPayload()
                    ->get('sub_mchid', $config->get('sub_mch_id', '')),
            ]);
        }
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'v3/profitsharing/return-orders';
    }
}
