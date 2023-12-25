<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Mini;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/mini-program-payment/close-order.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-mini-program-payment/close-order.html
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
        Logger::debug('[Wechat][Pay][Mini][ClosePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        if (empty($payload?->get('out_trade_no') ?? null)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Mini 关闭订单，参数缺少 `out_trade_no`');
        }

        if (Pay::MODE_SERVICE === $config['mode']) {
            $data = $this->service($config);
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/pay/transactions/out-trade-no/'.$payload->get('out_trade_no').'/close',
                '_service_url' => 'v3/pay/partner/transactions/out-trade-no/'.$payload->get('out_trade_no').'/close',
            ],
            $data ?? $this->normal($config)
        ));

        Logger::info('[Wechat][Pay][Mini][ClosePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $config): array
    {
        return [
            'mchid' => $config['mch_id'] ?? '',
        ];
    }

    protected function service(array $config): array
    {
        return [
            'sp_mchid' => $config['mch_id'] ?? '',
            'sub_mchid' => $config['sub_mch_id'] ?? '',
        ];
    }
}
