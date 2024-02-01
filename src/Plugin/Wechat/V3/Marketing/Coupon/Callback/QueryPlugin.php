<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Callback;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/call-back-url/query-callback.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/call-back-url/query-callback.html
 */
class QueryPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Coupon][Callback][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $mchId = $rocket->getPayload()?->get('mchid') ?? $config['mch_id'] ?? 'null';

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/callbacks?mchid='.$mchId,
            '_service_url' => 'v3/marketing/favor/callbacks?mchid='.$mchId,
        ]);

        Logger::info('[Wechat][Marketing][Coupon][Callback][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
