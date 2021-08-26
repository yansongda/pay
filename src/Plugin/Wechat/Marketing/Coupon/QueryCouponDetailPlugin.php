<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class QueryCouponDetailPlugin extends GeneralPlugin
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
        $appid = get_wechat_config($rocket->getParams())->get('mp_app_id');

        if (is_null($payload->get('coupon_id')) ||
            is_null($payload->get('openid'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/marketing/favor/users/'.
            $payload->get('openid').
            'coupons/'.$payload->get('coupon_id').
            '?appid='.$appid;
    }
}
