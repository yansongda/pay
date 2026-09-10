<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1\Pay\Web;

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
 * @see https://mapi.bestpay.com.cn/gapi/telecomPortal/getApiDocument?productCode=1008 超级收银台 /pay/tradeCreate（WEBCASHIER）
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
        Logger::debug('[Bestpay][V1][Pay][Web][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var BestpayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_BESTPAY, $params);

        $rocket->mergePayload([
            '_path' => '/pay/tradeCreate',
            '_method' => 'POST',
            'tradeChannel' => 'WEBCASHIER',
            'accessCode' => 'CASHIER',
            'ccy' => $rocket->getPayload()?->get('ccy') ?? '156',
            'notifyUrl' => $rocket->getPayload()?->get('notifyUrl') ?? $config->getNotifyUrl(),
            'returnUrl' => $rocket->getPayload()?->get('returnUrl') ?? $config->getReturnUrl(),
        ]);

        Logger::info('[Bestpay][V1][Pay][Web][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
