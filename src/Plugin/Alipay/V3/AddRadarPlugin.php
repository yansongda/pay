<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_alipay_body;
use function Yansongda\Pay\get_alipay_method;
use function Yansongda\Pay\get_alipay_url;
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
        Logger::debug('[Alipay][V3][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_provider_config('alipay', $params);

        $rocket->setRadar(new Request(
            get_alipay_method($payload),
            get_alipay_url($config, $payload),
            $this->getHeaders($payload),
            get_alipay_body($payload),
        ));

        Logger::info('[Alipay][V3][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(?Collection $payload): array
    {
        $headers = array_merge([
            'Accept' => 'application/json, text/plain, application/x-gzip',
            'User-Agent' => 'yansongda/pay-v3',
            'Content-Type' => 'application/json; charset=utf-8',
        ], $payload?->get('_headers', []) ?? []);

        if (!empty($authorization = $payload?->get('_authorization'))) {
            $headers['Authorization'] = $authorization;
        }

        if (!empty($accept = $payload?->get('_accept'))) {
            $headers['Accept'] = $accept;
        }

        if (!empty($contentType = $payload?->get('_content_type'))) {
            $headers['Content-Type'] = $contentType;
        }

        return $headers;
    }
}
