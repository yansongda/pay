<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AlipayTrait;

/**
 * 付款码支付（被扫码）.
 *
 * @see https://opendocs.alipay.com/open-v3/08c7f9f8_alipay.trade.pay
 */
class PosPlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][PosPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALIPAY, $rocket->getParams());

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 付款码支付（Pos），参数为空');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/pay',
            'notify_url' => $payload->get('notify_url', $config->getNotifyUrl()),
        ]);

        Logger::info('[Alipay][V3][PosPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
