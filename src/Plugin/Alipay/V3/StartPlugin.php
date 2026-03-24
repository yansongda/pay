<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

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
        Logger::debug('[Alipay][V3][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload($this->getPayload($rocket->getParams()));

        Logger::info('[Alipay][V3][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function getPayload(array $params): array
    {
        $config = get_provider_config('alipay', $params);

        return array_filter([
            'notify_url' => $this->getNotifyUrl($params, $config),
            'app_auth_token' => $this->getAppAuthToken($params, $config),
        ]);
    }

    protected function getNotifyUrl(array $params, array $config): ?string
    {
        return ($params['_notify_url'] ?? null) ?: ($config['notify_url'] ?? null) ?: null;
    }

    protected function getAppAuthToken(array $params, array $config): ?string
    {
        return ($params['_app_auth_token'] ?? null) ?: ($config['app_auth_token'] ?? null) ?: null;
    }
}
