<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayV3Config;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AlipayTrait;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\get_radar_method;

/**
 * 支付宝 V3 请求签名插件：生成 `Authorization` header 并写入 payload.
 *
 * @see https://opendocs.alipay.com/open-v3/05419m 支付宝支付签名生成算法
 */
class AddPayloadSignaturePlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][AddPayloadSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var AlipayV3Config $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALIPAY, $params);
        $payload = $rocket->getPayload();

        $appAuthToken = $this->getAppAuthToken($params, $config);

        $rocket->mergePayload([
            '_authorization' => self::getAlipayV3Authorization(
                $config,
                get_radar_method($payload) ?? 'POST',
                $this->getSignatureUri($config, $payload),
                (string) ($payload?->get('_body') ?? ''),
                $appAuthToken,
            ),
        ]);

        if (!empty($appAuthToken)) {
            $rocket->mergePayload(['_app_auth_token' => $appAuthToken]);
        }

        Logger::info('[Alipay][V3][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * 提取签名组串中的 requestUri（path+query，不含 host）.
     */
    protected function getSignatureUri(AlipayV3Config $config, ?Collection $payload): string
    {
        $url = self::getAlipayV3Url($config, $payload);
        $urlPath = parse_url($url, PHP_URL_PATH);
        $urlQuery = parse_url($url, PHP_URL_QUERY);

        return ($urlPath ?? '').(empty($urlQuery) ? '' : '?'.$urlQuery);
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function getAppAuthToken(array $params, AlipayV3Config $config): string
    {
        if (!empty($params['_app_auth_token'])) {
            return $params['_app_auth_token'];
        }

        return $config->getAppAuthToken() ?? '';
    }
}
