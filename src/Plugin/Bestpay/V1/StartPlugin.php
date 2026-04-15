<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

use function Yansongda\Pay\get_provider_config;

class StartPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Bestpay][V1][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('bestpay', $params);

        $rocket->mergePayload(array_merge(
            ['signType' => 'MD5'],
            array_filter($params, fn ($v, $k) => !str_starts_with((string) $k, '_'), ARRAY_FILTER_USE_BOTH),
            [
                'merchantNo' => $config['merchant_no'] ?? '',
                'platform' => $config['platform'] ?? '',
                'requestTimestamp' => date('YmdHis'),
            ],
        ));

        Logger::info('[Bestpay][V1][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
