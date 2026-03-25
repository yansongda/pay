<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use Throwable;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_alipay_v3_url;
use function Yansongda\Pay\get_private_cert;
use function Yansongda\Pay\get_provider_config;

class AddPayloadSignaturePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws Throwable
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][AddPayloadSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $timestamp = $this->getCurrentMillis();
        $nonce = Str::random(32);
        $requestId = $payload?->get('_request_id', $this->createRequestId()) ?? $this->createRequestId();
        $authorization = $this->getAuthorization($rocket, $payload, $timestamp, $nonce);
        $headers = $payload?->get('_headers', []);

        $headers['Authorization'] = $authorization;
        $headers['alipay-request-id'] = $requestId;

        if (!empty($appAuthToken = $payload?->get('app_auth_token', ''))) {
            $headers['alipay-app-auth-token'] = $appAuthToken;
        }

        $rocket->mergePayload([
            '_headers' => $headers,
            '_request_id' => $requestId,
        ]);

        Logger::info('[Alipay][V3][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function getAuthorization(Rocket $rocket, ?Collection $payload, string $timestamp, string $nonce): string
    {
        $auth = [
            'app_id='.$payload?->get('app_id', ''),
        ];

        if (!empty($appCertSn = $payload?->get('app_cert_sn', ''))) {
            $auth[] = 'app_cert_sn='.$appCertSn;
        }

        $auth[] = 'nonce='.$nonce;
        $auth[] = 'timestamp='.$timestamp;

        $authString = implode(',', $auth);
        $contents = $this->getSignatureContent($rocket, $payload, $authString);
        $privateKey = $this->getPrivateKey($rocket->getParams());

        openssl_sign($contents, $sign, $privateKey, OPENSSL_ALGO_SHA256);

        return 'ALIPAY-SHA256withRSA '.$authString.',sign='.base64_encode($sign);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getSignatureContent(Rocket $rocket, ?Collection $payload, string $authString): string
    {
        $config = get_provider_config('alipay', $rocket->getParams());
        $url = get_alipay_v3_url($config, $payload);
        $urlPath = parse_url($url, PHP_URL_PATH);
        $urlQuery = parse_url($url, PHP_URL_QUERY);
        $body = $this->getBody($payload);
        $appAuthToken = $payload?->get('app_auth_token', '');

        return $authString."\n"
            .strtoupper($payload?->get('_method', 'POST'))."\n"
            .$urlPath.(empty($urlQuery) ? '' : '?'.$urlQuery)."\n"
            .$body."\n"
            .(empty($appAuthToken) ? '' : $appAuthToken."\n");
    }

    protected function getBody(?Collection $payload): string
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

    protected function getCurrentMillis(): string
    {
        [$micro, $second] = explode(' ', microtime());

        return sprintf('%d%03d', intval($second), intval(floatval($micro) * 1000));
    }

    protected function createRequestId(): string
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr(md5(uniqid((string) mt_rand(), true)), 0, 8),
            substr(md5(uniqid((string) mt_rand(), true)), 8, 4),
            substr(md5(uniqid((string) mt_rand(), true)), 12, 4),
            substr(md5(uniqid((string) mt_rand(), true)), 16, 4),
            substr(md5(uniqid((string) mt_rand(), true)), 20, 12),
        );
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getPrivateKey(array $params): string
    {
        $privateKey = get_provider_config('alipay', $params)['app_secret_cert'] ?? null;

        if (is_null($privateKey)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [app_secret_cert]');
        }

        return get_private_cert($privateKey);
    }
}
