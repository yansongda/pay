<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay\Pay\Scan;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Pay\get_provider_config;

class PrepayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Epay][Pay][Scan][PrepayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $backUrl = $rocket->getPayload()['notify_url'] ?? null;
        if (empty($backUrl)) {
            $params = $rocket->getParams();
            $config = get_provider_config('epay', $params);

            $backUrl = $config['notify_url'] ?? null;
        }

        if (!$backUrl) {
            throw new InvalidConfigException(Exception::CONFIG_EPAY_INVALID, 'Missing Epay Config -- [notify_url]');
        }
        $rocket->mergePayload([
            'service' => 'atPay',
            'backUrl' => $backUrl,
        ]);

        Logger::info('[Epay][Pay][Scan][PrepayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
