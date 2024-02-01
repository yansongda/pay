<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Coupons;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/coupon/list-coupons-by-filter.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/coupon/list-coupons-by-filter.html
 */
class QueryUserPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Coupon][Coupons][QueryUserPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();
        $openId = $payload?->get('openid') ?? null;

        if (empty($openId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 根据商户号查用户的券，参数缺少 `openid`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/users/'.$openId.'/coupons?'.$this->normal($payload, $params, $config),
            '_service_url' => 'v3/marketing/favor/users/'.$openId.'/coupons?'.$this->normal($payload, $params, $config),
        ]);

        Logger::info('[Wechat][V3][Marketing][Coupon][Coupons][QueryUserPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(Collection $payload, array $params, array $config): string
    {
        $appId = $payload->get('appid');

        if (is_null($appId)) {
            $payload->set('appid', $config[get_wechat_type_key($params)] ?? '');
        }

        return filter_params($payload)->except('openid')->query();
    }
}
