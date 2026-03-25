<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Bestpay\V1;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Pay\get_provider_config;

class AddPayloadSignPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Bestpay][V1][AddPayloadSignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('bestpay', $params);
        $payload = $rocket->getPayload();

        $appKey = $config['app_key'] ?? null;

        if (empty($appKey)) {
            throw new InvalidConfigException(Exception::CONFIG_BESTPAY_INVALID, '配置异常: 缺少翼支付配置 -- [app_key]');
        }

        $rocket->mergePayload(['sign' => $this->getSign($payload->all(), $appKey)]);

        Logger::info('[Bestpay][V1][AddPayloadSignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getSign(array $params, string $appKey): string
    {
        $filtered = array_filter($params, fn ($v, $k) => '' !== $v && null !== $v && 'sign' !== $k && 'signType' !== $k, ARRAY_FILTER_USE_BOTH);
        ksort($filtered);

        $queryString = http_build_query($filtered).'&key='.$appKey;

        return strtolower(md5($queryString));
    }
}
