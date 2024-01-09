<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/stock/list-available-merchants.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/stock/list-available-merchants.html
 */
class QueryStockMerchantsPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Coupon][QueryStockMerchantsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();
        $stockId = $payload?->get('stock_id') ?? null;

        if (empty($stockId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询代金券可用商户，参数缺少 `stock_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/stocks/'.$stockId.'/merchants?'.$this->normal($payload, $config),
            '_service_url' => 'v3/marketing/favor/stocks/'.$stockId.'/merchants?'.$this->normal($payload, $config),
        ]);

        Logger::info('[Wechat][V3][Marketing][Coupon][QueryStockMerchantsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    public function normal(Collection $payload, array $config): string
    {
        $stockCreatorMchId = $payload->get('stock_creator_mchid');

        if (is_null($stockCreatorMchId)) {
            $payload->set('stock_creator_mchid', $config['mch_id'] ?? '');
        }

        return $payload->except('stock_id')->query();
    }
}
