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
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/coupon/send-coupon.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/coupon/send-coupon.html
 */
class SendPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Coupon][Coupons][SendPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();
        $openId = $payload?->get('openid') ?? null;

        if (empty($openId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 发放指定批次的代金券，参数缺少 `openid`');
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/marketing/favor/users/'.$openId.'/coupons',
                '_service_url' => 'v3/marketing/favor/users/'.$openId.'/coupons',
            ],
            $this->normal($payload, $params, $config),
        ));

        Logger::info('[Wechat][V3][Marketing][Coupon][Coupons][SendPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(Collection $payload, array $params, WechatConfig $config): array
    {
        if (empty($payload->get('appid'))) {
            $payload->set('appid', $config->getAppIdByType($params['_type'] ?? 'mp') ?? '');
        }

        if (empty($payload->get('stock_creator_mchid'))) {
            $payload->set('stock_creator_mchid', $config->getMchId());
        }

        return $payload->except('openid')->all();
    }
}
