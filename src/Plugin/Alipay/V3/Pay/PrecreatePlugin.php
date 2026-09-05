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
use Yansongda\Pay\Config\AlipayV3Config;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\AlipayTrait;

/**
 * 扫码支付（主动扫，预下单生成二维码）.
 *
 * @see https://opendocs.alipay.com/open-v3/fa0c2141_alipay.trade.precreate
 */
class PrecreatePlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][PrecreatePlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        /** @var AlipayV3Config $config */
        $config = self::getProviderConfig('alipay', $rocket->getParams());

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 扫码支付（Precreate），参数为空');
        }

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/trade/precreate',
            'notify_url' => $payload->get('notify_url', $config->getNotifyUrl()),
        ]);

        Logger::info('[Alipay][V3][PrecreatePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
