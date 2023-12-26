<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_config_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/coupon/query-coupon.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/coupon/query-coupon.html
 */
class QueryCouponDetailPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Coupon][QueryCouponDetailPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        if (empty($payload?->get('openid') ?? null)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询代金券详情，参数缺少 `openid`');
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'GET',
                '_url' => 'v3/marketing/favor/users/'.$payload->get('openid').'/coupons/'.$payload->get('coupon_id').'?appid='.$config[get_wechat_config_type_key($params)],
                '_service_url' => 'v3/marketing/favor/users/'.$payload->get('openid').'/coupons'.$payload->get('coupon_id').'?appid='.$config[get_wechat_config_type_key($params)],
            ],
        ));

        Logger::info('[Wechat][Marketing][Coupon][QueryCouponDetailPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
