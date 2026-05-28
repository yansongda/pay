<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Traits\WechatTrait;

class AddPayloadSignaturePlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][AddPayloadSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);

        $uri = $payload->get('_url', '');
        $body = self::getWechatBody($payload);
        $env = (int) $payload->get('env', 0);

        $paySig = self::getWechatVirtualPaySignature($config, $uri, $body, $env);

        $queryParams = [
            'access_token' => $payload->get('_access_token', ''),
            'pay_sig' => $paySig,
        ];

        $sessionKey = $params['_session_key'] ?? $payload->get('_session_key');

        if (!empty($sessionKey)) {
            $queryParams['signature'] = self::getWechatVirtualSessionSignature($sessionKey, $body);
        }

        $rocket->mergePayload([
            '_url' => $this->appendQueryParams($uri, $queryParams),
        ]);

        Logger::info('[Wechat][Virtual][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function appendQueryParams(string $uri, array $params): string
    {
        $separator = str_contains($uri, '?') ? '&' : '?';

        return $uri.$separator.http_build_query($params);
    }
}
