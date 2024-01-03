<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/call-back-url/query-callback.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/call-back-url/query-callback.html
 */
class QueryCallbackPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Coupon][QueryCallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $mchId = $rocket->getPayload()?->get('mchid') ?? $config['mch_id'] ?? 'null';

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/callbacks?mchid='.$mchId,
            '_service_url' => 'v3/marketing/favor/callbacks?mchid='.$mchId,
        ]);

        Logger::info('[Wechat][Marketing][Coupon][QueryCallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
