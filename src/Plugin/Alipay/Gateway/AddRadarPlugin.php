<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Gateway;

use Closure;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\get_radar_method;
use function Yansongda\Pay\get_alipay_url;
use function Yansongda\Pay\get_provider_config;

class AddRadarPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][Gateway][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_provider_config('alipay', $params);
        $payload = $rocket->getPayload();

        $rocket->setRadar(new Request(
            get_radar_method(new Collection($params)) ?? 'POST',
            get_alipay_url($config, $payload),
            $this->getHeaders($params),
            $this->getBody($payload, $params)
        ));

        Logger::info('[Alipay][Gateway][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(array $params): array
    {
        if (!empty($params['_multipart'])) {
            return [];
        }

        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'User-Agent' => 'yansongda/pay-v3',
        ];
    }

    protected function getBody(?Collection $payload, array $params): MultipartStream|string
    {
        if (!empty($params['_multipart'])) {
            $multipartData = $params['_multipart'];
            foreach ($payload as $name => $value) {
                $multipartData[] = [
                    'name' => $name,
                    'contents' => $value,
                ];
            }

            return new MultipartStream($multipartData, uniqid('alipay_', true));
        }

        return $payload?->query() ?? '';
    }
}
