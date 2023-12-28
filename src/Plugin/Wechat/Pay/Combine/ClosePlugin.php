<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Combine;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/orders/close-order.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/combine-payment/orders/close-order.html
 */
class ClosePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Combine][ClosePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $combineOutTradeNo = $rocket->getPayload()?->get('combine_out_trade_no') ?? null;

        if (empty($combineOutTradeNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 合单关单，参数缺少 `combine_out_trade_no`');
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/combine-transactions/out-trade-no/'.$combineOutTradeNo.'/close',
                '_service_url' => 'v3/combine-transactions/out-trade-no/'.$combineOutTradeNo.'/close',
            ],
            $this->normal($config, $params)
        ));

        Logger::info('[Wechat][Pay][Combine][ClosePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $config, array $params): array
    {
        return [
            'combine_appid' => $config[get_wechat_type_key($params)] ?? '',
        ];
    }
}
