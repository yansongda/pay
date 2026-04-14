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
use Yansongda\Supports\Collection;

use function Yansongda\Artful\get_radar_method;
use function Yansongda\Pay\get_airwallex_url;
use function Yansongda\Pay\get_provider_config;

class AddRadarPlugin implements PluginInterface
{
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
        $config = get_provider_config('airwallex', $params);

        $rocket->setRadar(new Request(
            get_radar_method($payload),
            get_airwallex_url($config, $payload),
            $this->getHeaders($config, $payload),
            $this->getBody($payload),
        ));

        Logger::info('[Airwallex][V1][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(array $config, ?Collection $payload): array
    {
        $headers = [
            'User-Agent' => 'yansongda/pay-v3',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ('client' === $payload?->get('_auth_type')) {
            $headers['x-client-id'] = $config['client_id'] ?? '';
            $headers['x-api-key'] = $config['api_key'] ?? '';
        } else {
            $headers['Authorization'] = 'Bearer '.($payload?->get('_access_token') ?? '');
        }

        if (!empty($config['api_version'])) {
            $headers['x-api-version'] = $config['api_version'];
        }

        $onBehalfOf = $payload?->get('_on_behalf_of') ?? $config['on_behalf_of'] ?? null;

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
