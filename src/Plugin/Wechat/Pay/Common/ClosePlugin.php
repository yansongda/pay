<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class ClosePlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/pay/transactions/out-trade-no/'.
            ($rocket->getParams()['out_trade_no'] ?? '').
            '/close';
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function checkPayload(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        $rocket->setPayload(new Collection([
            'mchid' => $config->get('mch_id', ''),
        ]));
    }
}
