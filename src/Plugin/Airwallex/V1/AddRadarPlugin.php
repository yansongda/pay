<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Airwallex\V1;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AirwallexConfig;
use Yansongda\Pay\Traits\AirwallexTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\get_radar_method;

/**
 * @see https://www.airwallex.com/docs/api/introduction
 */
class AddRadarPlugin implements PluginInterface
{
    use AirwallexTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Airwallex][V1][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var AirwallexConfig $config */
        $config = self::getProviderConfig('airwallex', $params);

        $rocket->setRadar(new Request(
            get_radar_method($payload),
            self::getAirwallexUrl($config, $payload),
            $this->getHeaders($config, $payload),
            $this->getBody($payload),
        ));

        Logger::info('[Airwallex][V1][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(AirwallexConfig $config, ?Collection $payload): array
    {
        $headers = [
            'User-Agent' => 'yansongda/pay-v3',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ('client' === $payload?->get('_auth_type')) {
            $headers['x-client-id'] = $config->getClientId();
            $headers['x-api-key'] = $config->getApiKey();
        } else {
            $headers['Authorization'] = 'Bearer '.($payload?->get('_access_token') ?? '');
        }

        if (!empty($config->getApiVersion())) {
            $headers['x-api-version'] = $config->getApiVersion();
        }

        $onBehalfOf = $payload?->get('_on_behalf_of') ?? $config->getOnBehalfOf();

        if (!empty($onBehalfOf)) {
            $headers['x-on-behalf-of'] = $onBehalfOf;
        }

        return $headers;
    }

    protected function getBody(?Collection $payload): string
    {
        return $payload?->get('_body') ?? '';
    }
}
