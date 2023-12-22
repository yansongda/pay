<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Balance;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class QueryPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function doSomething(Rocket $rocket): void {}

    /**
     * @throws InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (!$payload->has('account_type')) {
            throw new InvalidParamsException(Exception::NECESSARY_PARAMS_MISSING);
        }

        return 'v3/merchant/fund/balance/'.
            $payload->get('account_type');
    }
}
