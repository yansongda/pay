<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Jsapi;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/close-order.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-jsapi-payment/close-order.html
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
        Logger::debug('[Wechat][Pay][Jsapi][ClosePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();
        $outTradeNo = $payload?->get('out_trade_no') ?? null;

        if (empty($outTradeNo)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Jsapi 关闭订单，参数缺少 `out_trade_no`');
        }

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            $data = $this->service($payload, $config);
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/out-trade-no/'.$outTradeNo.'/close',
                '_service_url' => 'v3/pay/partner/transactions/out-trade-no/'.$outTradeNo.'/close',
            ],
            $data ?? $this->normal($payload, $config)
        ));

        Logger::info('[Wechat][Pay][Jsapi][ClosePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(Collection $payload, array $config): array
    {
        return [
            'mchid' => $payload->get('mchid', $config['mch_id'] ?? ''),
        ];
    }

    protected function service(Collection $payload, array $config): array
    {
        return [
            'sp_mchid' => $payload->get('sp_mchid', $config['mch_id'] ?? ''),
            'sub_mchid' => $payload->get('sub_mchid', $config['sub_mch_id'] ?? ''),
        ];
    }
}
