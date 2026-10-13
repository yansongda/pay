<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Apple;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AppleTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use function Yansongda\Artful\get_radar_headers;
use function Yansongda\Artful\get_radar_method;

class AddRadarPlugin implements PluginInterface
{
    use AppleTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Apple][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var AppleConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_APPLE, $params);

        $rocket->setRadar(new Request(
            get_radar_method($payload) ?? 'POST',
            self::getAppleUrl($config, $payload).$this->getQueryString($payload),
            $this->getHeaders($payload, $params),
            (string) ($payload?->get('_body') ?? ''),
        ));

        Logger::info('[Apple][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, string>
     */
    protected function getHeaders(?Collection $payload, array $params): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        // `validate_url`/`merchantSession` 等本地校验场景通过 `_no_jwt` 跳过 JWT 生成
        if (empty($payload?->get('_no_jwt'))) {
            $headers['Authorization'] = 'Bearer '.self::generateAppleJwt($params);
        }

        // 支持通过 `_headers` 注入自定义请求头（如 `Idempotency-Key`），可覆盖默认值
        $customHeaders = get_radar_headers($payload);

        if (is_array($customHeaders)) {
            $headers = array_merge($headers, $customHeaders);
        }

        return $headers;
    }

    /**
     * 显式 `_query` 参数拼接到 URL；URL 已含 `?`（如 subscriptions `_url` 自带 `?status=1&status=4`）时用 `&` 连接。
     */
    protected function getQueryString(?Collection $payload): string
    {
        $query = $payload?->get('_query');

        if (!is_array($query) || [] === $query) {
            return '';
        }

        $queryString = http_build_query(filter_params($query)->toArray());

        if (str_contains((string) $payload->get('_url'), '?')) {
            return '&'.$queryString;
        }

        return '?'.$queryString;
    }
}
