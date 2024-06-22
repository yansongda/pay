<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Jsb\Pay\Scan;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Pay\get_provider_config;

/**
 * @see https://github.com/yansongda/pay/pull/1002
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     * @throws ContainerException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Jsb][Pay][Scan][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('jsb', $params);
        $backUrl = $rocket->getPayload()['notify_url'] ?? $config['notify_url'] ?? null;

        if (!$backUrl) {
            throw new InvalidConfigException(Exception::CONFIG_JSB_INVALID, '配置异常: 缺少配置参数 -- [notify_url]');
        }

        $rocket->mergePayload([
            'service' => 'atPay',
            'backUrl' => $backUrl,
        ]);

        Logger::info('[Jsb][Pay][Scan][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
