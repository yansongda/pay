<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1\Pay\Scan;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\BestpayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\BestpayTrait;

/**
 * @see https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocument?productCode=1006 线下聚合 /aggregate/aggregatepay/offline/c2b/payOrder
 */
class PayPlugin implements PluginInterface
{
    use BestpayTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Bestpay][V1][Pay][Scan][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var BestpayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_BESTPAY, $params);

        $rocket->mergePayload([
            '_path' => '/aggregate/aggregatepay/offline/c2b/payOrder',
            'notifyUrl' => $rocket->getPayload()?->get('notifyUrl') ?? $config->getNotifyUrl(),
        ]);

        Logger::info('[Bestpay][V1][Pay][Scan][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
