<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class QueryPlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();

        if (!is_null($payload->get('transaction_id'))) {
            return 'v3/pay/transactions/id/'.
                $payload->get('transaction_id').
                '?mchid='.$config->get('mch_id', '');
        }

        if (!is_null($payload->get('out_trade_no'))) {
            return 'v3/pay/transactions/out-trade-no/'.
                $payload->get('out_trade_no').
                '?mchid='.$config->get('mch_id', '');
        }

        throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getPartnerUri(Rocket $rocket): string
    {
        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();

        if (!is_null($payload->get('transaction_id'))) {
            return 'v3/pay/partner/transactions/id/'.
                $payload->get('transaction_id').
                '?sp_mchid='.$config->get('mch_id', '').
                '&sub_mchid='.$payload->get('sub_mchid', $config->get('sub_mch_id'));
        }

        if (!is_null($payload->get('out_trade_no'))) {
            return 'v3/pay/partner/transactions/out-trade-no/'.
                $payload->get('out_trade_no').
                '?sp_mchid='.$config->get('mch_id', '').
                '&sub_mchid='.$payload->get('sub_mchid', $config->get('sub_mch_id'));
        }

        throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
    }

    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setPayload(null);
    }
}
