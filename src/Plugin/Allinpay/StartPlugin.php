<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Allinpay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Packer\JsonPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AllinpayTrait;
use Yansongda\Supports\Str;

class StartPlugin implements PluginInterface
{
    use AllinpayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Allinpay][StartPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var AllinpayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALLINPAY, $params);

        $rocket->setPacker(JsonPacker::class)
            ->mergePayload(array_merge($params, [
                'cusid' => $config->getCusid(),
                'appid' => $config->getAppid(),
                'orgid' => $config->getOrgid(),
                'signtype' => 'RSA',
                'version' => $params['version'] ?? '11',
                'randomstr' => Str::random(20),
                'notifyurl' => $this->getNotifyUrl($params, $config),
            ]));

        Logger::info('[Allinpay][StartPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function getNotifyUrl(array $params, AllinpayConfig $config): ?string
    {
        if (!empty($params['_notify_url'])) {
            return (string) $params['_notify_url'];
        }

        return $config->getNotifyUrl();
    }
}
