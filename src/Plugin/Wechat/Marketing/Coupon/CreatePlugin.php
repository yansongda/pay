<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Coupon;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/stock/create-coupon-stock.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/stock/create-coupon-stock.html
 */
class CreatePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Coupon][CreatePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $belongMerchant = $rocket->getPayload()?->get('belong_merchant') ?? $config['mch_id'];

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => 'v3/marketing/favor/coupon-stocks',
            '_service_url' => 'v3/marketing/favor/coupon-stocks',
            'belong_merchant' => $belongMerchant,
        ]);

        Logger::info('[Wechat][Marketing][Coupon][CreatePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
