<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing;

use Yansongda\Pay\Exception\InvalidParamsException;
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
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('out_order_no')) ||
            is_null($payload->get('transaction_id'))) {
            throw new InvalidParamsException(InvalidParamsException::MISSING_NECESSARY_PARAMS);
        }

        return '/v3/profitsharing/orders/'.
            $payload->get('out_order_no').
            '?transaction_id='.$payload->get('transaction_id');
    }
}
