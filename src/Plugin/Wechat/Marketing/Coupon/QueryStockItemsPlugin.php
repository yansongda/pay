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

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/cash-coupons/stock/list-available-singleitems.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/cash-coupons/stock/list-available-singleitems.html
 */
class QueryStockItemsPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Coupon][QueryStockItemsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        if (empty($payload?->get('stock_id') ?? null)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 查询代金券可用单品，参数缺少 `stock_id`');
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'GET',
                '_url' => 'v3/marketing/favor/stocks/'.$payload->get('stock_id').'/items?'.$this->normal($payload, $config),
                '_service_url' => 'v3/marketing/favor/stocks/'.$payload->get('stock_id').'/items?'.$this->normal($payload, $config),
            ],
        ));

        Logger::info('[Wechat][Marketing][Coupon][QueryStockItemsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    public function normal(?Collection $payload, array $config): string
    {
        return http_build_query(array_merge($payload?->all() ?? [], [
            'stock_creator_mchid' => $config['mch_id'],
        ]));
    }
}
