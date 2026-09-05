<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

use function Yansongda\Artful\get_radar_method;

class AddRadarPlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALIPAY, $rocket->getParams());

        $rocket->setRadar(new Request(
            get_radar_method($payload) ?? 'POST',
            self::getAlipayV3Url($config, $payload),
            $this->getHeaders($payload),
            $payload?->get('_body'),
        ));

        Logger::info('[Alipay][V3][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @return array<string, string>
     */
    protected function getHeaders(?Collection $payload): array
    {
        $headers = [
            'Accept' => 'application/json, text/plain, application/x-gzip',
            'User-Agent' => 'yansongda/pay-v3',
            'Content-Type' => 'application/json; charset=utf-8',
            // 官方 SDK 无条件携带，用于网关侧定位一次请求
            'alipay-request-id' => $this->getAlipayRequestId(),
        ];

        if (!empty($authorization = $payload?->get('_authorization'))) {
            $headers['Authorization'] = $authorization;
        }

        if (!empty($appAuthToken = $payload?->get('_app_auth_token'))) {
            $headers['alipay-app-auth-token'] = $appAuthToken;
        }

        return $headers;
    }

    /**
     * 生成请求唯一 ID（UUID v4，加密安全随机源）.
     */
    protected function getAlipayRequestId(): string
    {
        return Str::uuidV4();
    }
}
