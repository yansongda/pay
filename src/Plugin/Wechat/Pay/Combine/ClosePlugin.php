<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Combine;

use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class ClosePlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\ClosePlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/combine-transactions/out-trade-no/'.
            ($rocket->getParams()['combine_out_trade_no'] ?? $rocket->getParams()['out_trade_no'] ?? '').
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
            'combine_appid' => $config->get('combine_appid', ''),
            'sub_orders' => $rocket->getParams()['sub_orders'] ?? [],
        ]));
    }
}
