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

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/stock/restart-stock.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/stock/restart-stock.html
 */
class RestartPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Coupon][RestartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        if (empty($payload?->get('stock_id') ?? null)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 激活代金券，参数缺少 `stock_id`');
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/marketing/favor/stocks/'.$payload->get('stock_id').'/restart',
                '_service_url' => 'v3/marketing/favor/stocks/'.$payload->get('stock_id').'/restart',
                'stock_creator_mchid' => $config['mch_id'],
            ],
        ));

        Logger::info('[Wechat][Marketing][Coupon][RestartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
