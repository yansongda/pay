<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\DouyinTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;

class AddRadarPlugin implements PluginInterface
{
    use DouyinTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var DouyinConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_DOUYIN, $params);

        $rocket->setRadar(new Request(
            'POST',
            self::getDouyinUrl($config, $payload),
            $this->getHeaders($payload),
            $this->getBody($payload),
        ));

        Logger::info('[Douyin][V1][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @return array<string, string>
     */
    protected function getHeaders(?Collection $payload): array
    {
        $headers = [
            'User-Agent' => 'yansongda/pay-v3',
            'Content-Type' => 'application/json',
        ];

        $token = $payload?->get('_access_token') ?? '';

        if (!empty($token)) {
            $headers['access-token'] = $token;
        }

        return $headers;
    }

    protected function getBody(?Collection $payload): string
    {
        // 业务管线中 AddPayloadBodyPlugin 已把 filter_params(payload) 打包进 `_body`，优先使用
        $body = $payload?->get('_body');

        if (!is_null($body)) {
            return (string) $body;
        }

        // token 子调用等无 AddPayloadBodyPlugin 的管线：回退到过滤后的 payload；仅剩 `_` 键则空请求体
        $filtered = filter_params($payload)->all();

        return [] === $filtered ? '' : json_encode($filtered, JSON_UNESCAPED_UNICODE);
    }
}
