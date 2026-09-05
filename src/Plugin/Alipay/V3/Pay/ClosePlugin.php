<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AlipayTrait;

/**
 * 支付宝 V3 统一收单交易关闭.
 *
 * 业务字段（`out_trade_no`/`trade_no` 二选一、`operator_id` 等）由调用方经订单参数直接透传；
 * `notify_url` 为官方 `AlipayTradeCloseModel` 独有字段，按调用方传入优先、租户配置回落注入.
 *
 * @see https://opendocs.alipay.com/open-v3/48ea518b_alipay.trade.close
 */
class ClosePlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][ClosePlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALIPAY, $params);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/close',
            'notify_url' => $payload?->get('notify_url', $config->getNotifyUrl()),
        ]);

        Logger::info('[Alipay][V3][ClosePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
