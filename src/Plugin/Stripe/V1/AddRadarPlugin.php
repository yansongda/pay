<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Stripe\V1;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Traits\StripeTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use function Yansongda\Artful\get_radar_method;

class AddRadarPlugin implements PluginInterface
{
    use StripeTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Stripe][V1][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var StripeConfig $config */
        $config = self::getProviderConfig('stripe', $params);

        $rocket->setRadar(new Request(
            get_radar_method($payload),
            self::getStripeUrl($config, $payload).$this->getQueryString($payload),
            $this->getHeaders($config, $payload),
            $this->getBody($payload),
        ));

        Logger::info('[Stripe][V1][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(StripeConfig $config, ?Collection $payload): array
    {
        $secretKey = $config->getSecretKey();

        $headers = [
            'User-Agent' => 'yansongda/pay-v3',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$secretKey,
        ];

        $method = strtoupper($payload?->get('_method') ?? 'GET');

        if ('GET' !== $method) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        return $headers;
    }

    protected function getBody(?Collection $payload): string
    {
        $method = strtoupper($payload?->get('_method') ?? 'GET');

        if ('GET' === $method) {
            return '';
        }

        return http_build_query(filter_params($payload)->toArray());
    }

    protected function getQueryString(?Collection $payload): string
    {
        $method = strtoupper($payload?->get('_method') ?? 'GET');

        if ('GET' !== $method) {
            return '';
        }

        $params = filter_params($payload)->toArray();

        return empty($params) ? '' : '?'.http_build_query($params);
    }
}
