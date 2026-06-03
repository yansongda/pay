<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
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

        $accessToken = $payload->get('access_token');
        $isClientSigning = 'requestVirtualPayment' === $uri;

        if (!$isClientSigning && empty($accessToken)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付缺少 access_token');
        }

        $paySig = self::getWechatVirtualPaySignature($config, $uri, $body, $env);

        $queryParams = [
            'pay_sig' => $paySig,
        ];

        if (!empty($accessToken)) {
            $queryParams['access_token'] = $accessToken;
        }

        $sessionKey = $params['_session_key'] ?? $payload->get('_session_key');
        $signature = null;

        if (!empty($sessionKey)) {
            $signature = self::getWechatVirtualSessionSignature($sessionKey, $body);
            $queryParams['signature'] = $signature;
        }

        $mergeData = [
            '_url' => $this->appendQueryParams($uri, $queryParams),
            'paySig' => $paySig,
        ];

        if (null !== $signature) {
            $mergeData['signature'] = $signature;
        }

        $rocket->mergePayload($mergeData);

        Logger::info('[Wechat][Virtual][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function appendQueryParams(string $uri, array $params): string
    {
        $separator = str_contains($uri, '?') ? '&' : '?';

        return $uri.$separator.http_build_query($params);
    }
}
