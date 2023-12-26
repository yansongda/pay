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
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_config_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/coupon/send-coupon.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/coupon/send-coupon.html
 */
class SendPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Coupon][SendPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();
        $openId = $payload?->get('openid') ?? null;

        if (empty($openId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 根据商户号查用户的券，参数缺少 `openid`');
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/marketing/favor/users/'.$openId.'/coupons?'.$this->normal($payload, $params, $config),
                '_service_url' => 'v3/marketing/favor/users/'.$openId.'/coupons?'.$this->normal($payload, $params, $config),
            ],
        ));

        Logger::info('[Wechat][Marketing][Coupon][SendPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(?Collection $payload, array $params, array $config): string
    {
        return http_build_query(array_merge($payload?->all() ?? [], [
            'appid' => $config[get_wechat_config_type_key($params)],
            'stock_creator_mchid' => $config['mch_id'],
        ]));
    }
}
