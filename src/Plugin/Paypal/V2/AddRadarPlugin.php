<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Paypal\V2;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\PaypalConfig;
use Yansongda\Pay\Traits\PaypalTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\get_radar_method;

class AddRadarPlugin implements PluginInterface
{
    use PaypalTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Paypal][V2][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = self::getProviderConfig('paypal', $params);

        $rocket->setRadar(new Request(
            get_radar_method($payload),
            self::getPaypalUrl($config, $payload),
            $this->getHeaders($config, $payload),
            $this->getBody($payload),
        ));

        Logger::info('[Paypal][V2][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(PaypalConfig $config, ?Collection $payload): array
    {
        $headers = [
            'User-Agent' => 'yansongda/pay-v3',
            'Accept' => 'application/json',
        ];

        if ('basic' === $payload?->get('_auth_type')) {
            $clientId = $config->getClientId();
            $appSecret = $config->getAppSecret();
            $headers['Authorization'] = 'Basic '.base64_encode($clientId.':'.$appSecret);
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        } else {
            $token = $payload?->get('_access_token') ?? '';
            $headers['Authorization'] = 'Bearer '.$token;
            $headers['Content-Type'] = 'application/json; charset=utf-8';
        }

        return $headers;
    }

    protected function getBody(?Collection $payload): string
    {
        if ('basic' === $payload?->get('_auth_type')) {
            return 'grant_type=client_credentials';
        }

        return $payload?->get('_body') ?? '';
    }
}
