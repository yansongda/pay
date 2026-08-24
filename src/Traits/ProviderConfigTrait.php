<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\Config\ProviderConfigInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

trait ProviderConfigTrait
{
    /**
     * @param array<string, mixed> $params
     */
    public static function getTenant(array $params = []): string
    {
        return (string) ($params['_config'] ?? 'default');
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public static function getProviderConfig(string $provider, array $params = []): ProviderConfigInterface
    {
        /** @var ConfigInterface $config */
        $config = Pay::get(ConfigInterface::class);

        $tenant = static::getTenant($params);
        $result = $config->get($provider.'.'.$tenant);

        if (null === $result) {
            throw new InvalidConfigException(
                Exception::CONFIG_PROVIDER_INVALID,
                "配置异常: {$provider}.{$tenant} 配置不存在"
            );
        }

        $result->validate();

        return $result;
    }

    public static function getRadarUrl(ProviderConfigInterface $config, ?Collection $payload): ?string
    {
        if (null === $payload) {
            return null;
        }

        return match ($config->getMode()) {
            Pay::MODE_SERVICE => $payload->get('_service_url', $payload->get('_url')),
            Pay::MODE_SANDBOX => $payload->get('_sandbox_url', $payload->get('_url')),
            default => $payload->get('_url'),
        };
    }
}
