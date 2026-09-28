<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Apple\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AppleConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\AppleTrait;

/**
 * Apple Pay 网页网关 merchantSession 代理插件。
 *
 * 从 params 取 `validation_url`（Apple JS `onvalidatemerchant` 回调提供），
 * 经 SSRF 白名单校验（仅 https + *.apple.com）后直连 Apple Pay 网关；
 * TLS 双向认证证书由业务方经 `_http` 透传（Artful ignite 已合并进 Guzzle Client options），插件不处理。
 */
class MerchantSessionPlugin implements PluginInterface
{
    use AppleTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Apple][Pay][MerchantSessionPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $url = $params['validation_url'] ?? '';

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Apple merchant session 缺 validation_url 参数');
        }

        $parts = parse_url($url);

        if (false === $parts
            || 'https' !== ($parts['scheme'] ?? '')
            || empty($parts['host'])
            || !str_ends_with(strtolower((string) $parts['host']), '.apple.com')
        ) {
            throw new InvalidParamsException(Exception::PARAMS_APPLE_URL_MISSING, '参数异常: Apple validation_url 非法');
        }

        /** @var AppleConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_APPLE, $params);

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => $url,
            '_no_jwt' => true,
            '_body' => json_encode([
                'merchantIdentifier' => $config->getMerchantId(),
                'displayName' => $params['display_name'] ?? $config->getMerchantId(),
                'initiative' => 'web',
                'initiativeContext' => $params['initiative_context'] ?? '',
            ]),
        ]);

        Logger::info('[Apple][Pay][MerchantSessionPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
