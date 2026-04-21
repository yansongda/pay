<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\Config\ProviderConfigInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

trait ProviderConfigTrait
{
    public static function getTenant(array $params = []): string
    {
        return (string) ($params['_config'] ?? 'default');
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public static function getProviderConfig(string $provider, array $params = []): ProviderConfigInterface
    {
        /** @var ConfigInterface $config */
        $config = Pay::get(ConfigInterface::class);

        return $config->get($provider.'.'.static::getTenant($params));
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
