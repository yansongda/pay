<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_alipay_v3_url;
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
        $body = $this->getBody($payload, $params);

        $rocket->setRadar(new Request(
            strtoupper($payload?->get('_method', 'POST') ?? 'POST'),
            get_alipay_v3_url($config, $payload),
            $this->getHeaders($payload, $params, $body),
            $body,
        ));

        Logger::info('[Alipay][V3][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getHeaders(?Collection $payload, array $params, string|MultipartStream $body): array
    {
        $headers = $payload?->get('_headers', []);

        if (!empty($params['_multipart'])) {
            if ($body instanceof MultipartStream) {
                $headers['Content-Type'] = 'multipart/form-data; boundary='.$body->getBoundary();
            }

            return $headers;
        }

        if (!empty($body) && !isset($headers['Content-Type']) && 'GET' !== strtoupper($payload->get('_method', 'POST'))) {
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }

    protected function getBody(?Collection $payload, array $params): string|MultipartStream
    {
        if (!empty($params['_multipart'])) {
            $multipartData = $params['_multipart'];
            $body = $this->formatBody($payload);

            if ('' !== $body) {
                array_unshift($multipartData, [
                    'name' => 'data',
                    'contents' => $body,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ]);
            }

            return new MultipartStream($multipartData, uniqid('alipay_', true));
        }

        return $this->formatBody($payload);
    }

    protected function formatBody(?Collection $payload): string
    {
        $body = $payload?->get('_body');

        if ($body instanceof Collection) {
            return json_encode($body->all(), JSON_UNESCAPED_UNICODE) ?: '';
        }

        if (is_array($body) || is_object($body)) {
            return json_encode($body, JSON_UNESCAPED_UNICODE) ?: '';
        }

        return is_string($body) ? $body : '';
    }
}
