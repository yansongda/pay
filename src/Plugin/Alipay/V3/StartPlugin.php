<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\StartPlugin as StartPluginV2;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\get_tenant;

class StartPlugin extends StartPluginV2
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getPayload(array $params): array
    {
        $tenant = get_tenant($params);
        $config = get_provider_config('alipay', $params);

        return [
            'app_id' => $config['app_id'] ?? '',
            'app_auth_token' => $this->getAppAuthToken($params, $config),
            'app_cert_sn' => $this->getAppCertSn($tenant, $config),
            '_method' => 'POST',
            '_headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'yansongda/pay-v3',
            ],
        ];
    }
}
