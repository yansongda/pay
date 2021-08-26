<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Combine;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class ClosePlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\ClosePlugin
{
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('combine_out_trade_no')) &&
            is_null($payload->get('out_trade_no'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/combine-transactions/out-trade-no/'.
            $payload->get('combine_out_trade_no', $payload->get('out_trade_no')).
            '/close';
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $config = get_wechat_config($rocket->getParams());

        $rocket->setPayload(new Collection([
            'combine_appid' => $config->get('combine_appid', ''),
            'sub_orders' => $rocket->getParams()['sub_orders'] ?? [],
        ]));
    }
}
