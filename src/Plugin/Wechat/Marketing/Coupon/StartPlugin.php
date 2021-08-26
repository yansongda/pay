<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class StartPlugin extends GeneralPlugin
{
    protected function doSomething(Rocket $rocket): void
    {
        $rocket->getPayload()->forget('openid');
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('stock_id'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/marketing/favor/stocks/'.$payload->get('stock_id').'/start';
    }
}
