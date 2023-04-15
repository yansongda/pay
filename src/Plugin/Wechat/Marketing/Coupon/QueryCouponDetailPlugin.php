<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter9_1_6.shtml
 */
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
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();
        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        if (!$payload->has('coupon_id') || !$payload->has('openid')) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/marketing/favor/users/'.
            $payload->get('openid').
            '/coupons/'.$payload->get('coupon_id').
            '?appid='.$payload->get('appid', $config[$this->getConfigKey($params)] ?? '');
    }
}
