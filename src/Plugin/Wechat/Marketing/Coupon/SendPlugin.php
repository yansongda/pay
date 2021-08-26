<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

class SendPlugin extends GeneralPlugin
{
    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setPayload(new Collection([
            'stock_creator_mchid' => $rocket->getPayload()->get('stock_creator_mchid'),
        ]));
    }

    /**
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();

        if (is_null($payload->get('openid'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/marketing/favor/users/'.$payload->get('openid').'/coupons';
    }
}
