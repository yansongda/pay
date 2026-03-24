<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Trade;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use function Yansongda\Artful\get_radar_method;
use function Yansongda\Pay\get_douyin_trade_url;
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
        Logger::debug('[Douyin][V1][Trade][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_provider_config('douyin', $params);

        $rocket->setRadar(new Request(
            get_radar_method($payload),
            get_douyin_trade_url($config, $payload).$this->getQueryString($payload),
            $this->getHeaders($payload),
            $this->getBody($payload),
        ));

        Logger::info('[Douyin][V1][Trade][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(?Collection $payload): array
    {
        $headers = [
            'User-Agent' => 'yansongda/pay-v3',
            'Content-Type' => 'application/json; charset=utf-8',
        ];

        $token = $payload?->get('_access_token') ?? '';

        if (!empty($token)) {
            $headers['access-token'] = $token;
        }

        return $headers;
    }

    protected function getBody(?Collection $payload): string
    {
        if ('GET' === strtoupper($payload?->get('_method', 'POST') ?? 'POST')) {
            return '';
        }

        return $payload?->get('_body') ?? '';
    }

    protected function getQueryString(?Collection $payload): string
    {
        if ('GET' !== strtoupper($payload?->get('_method', 'POST') ?? 'POST')) {
            return '';
        }

        $params = filter_params($payload)->toArray();

        return empty($params) ? '' : '?'.http_build_query($params);
    }
}
