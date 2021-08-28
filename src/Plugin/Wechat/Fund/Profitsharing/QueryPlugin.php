<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class QueryPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setPayload(null);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();
        $config = get_wechat_config($rocket->getParams());

        if (is_null($payload->get('out_order_no')) ||
            is_null($payload->get('transaction_id'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        $url = 'v3/profitsharing/orders/'.
            $payload->get('out_order_no').
            '?transaction_id='.$payload->get('transaction_id');

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            $url .= '&sub_mchid='.$payload->get('sub_mchid', $config->get('sub_mch_id', ''));
        }

        return $url;
    }
}
