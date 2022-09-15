<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;

use function Yansongda\Pay\get_tenant;
use function Yansongda\Pay\get_unipay_config;

use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\GetUnipayCerts;
use Yansongda\Supports\Str;

class PreparePlugin implements PluginInterface
{
    use GetUnipayCerts;

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[unipay][PreparePlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload($this->getPayload($rocket->getParams()));

        Logger::info('[unipay][PreparePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function getPayload(array $params): array
    {
        $tenant = get_tenant($params);
        $config = get_unipay_config($params);

        $init = [
            'version' => '5.1.0',
            'encoding' => 'utf-8',
            'backUrl' => $config['notify_url'] ?? '',
            'currencyCode' => '156',
            'accessType' => '0',
            'signature' => '',
            'signMethod' => '01',
            'merId' => $config['mch_id'] ?? '',
            'frontUrl' => $config['return_url'] ?? '',
            'certId' => $this->getCertId($tenant, $config),
        ];

        return array_merge(
            $init,
            array_filter($params, fn ($v, $k) => !Str::startsWith(strval($k), '_'), ARRAY_FILTER_USE_BOTH),
        );
    }
}
