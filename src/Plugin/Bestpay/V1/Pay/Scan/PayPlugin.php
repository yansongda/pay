<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1\Pay\Scan;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://render.bestpay.cn/open-developers/index.html#/documentCenterLayout/accessProcess
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Bestpay][V1][Pay][Scan][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('bestpay', $params);

        $rocket->mergePayload([
            '_url' => 'pay/createQrPay',
            '_method' => 'POST',
            'backUrl' => $rocket->getPayload()?->get('backUrl') ?? $config['notify_url'] ?? '',
        ]);

        Logger::info('[Bestpay][V1][Pay][Scan][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
