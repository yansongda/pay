<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Transfer;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class CreatePlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        $wechatId = [
            'appid' => $config->get('mp_app_id'),
        ];

        $rocket->mergePayload($wechatId);
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'v3/transfer/batches';
    }
}
