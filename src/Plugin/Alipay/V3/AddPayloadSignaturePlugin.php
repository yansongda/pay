<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use DateTime;
use Throwable;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;
use function Yansongda\Pay\get_alipay_body;
use function Yansongda\Pay\get_alipay_app_public_cert_sn;
use function Yansongda\Pay\get_alipay_root_cert_sn;
use function Yansongda\Pay\get_alipay_method;
use function Yansongda\Pay\get_alipay_sign;
use function Yansongda\Pay\get_alipay_url;
use function Yansongda\Pay\get_config_value;
use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\get_tenant;

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
        $tenant = get_tenant($rocket->getParams());
        $config = get_provider_config('alipay', $rocket->getParams());
        $authorizationString = $this->getAuthorizationString($tenant, $config, $payload);
        $signatureContent = $this->getSignatureContent($authorizationString, $config, $payload);

        $headers = $payload?->get('_headers', []);
        $headers['authorization'] = 'ALIPAY-SHA256withRSA '.$authorizationString.',sign='.get_alipay_sign($config, $signatureContent);
        $headers['alipay-request-id'] = Str::random(32);
        $headers['alipay-root-cert-sn'] = get_alipay_root_cert_sn($tenant, $config);

        if (!empty($appAuthToken = get_config_value('app_auth_token', $config, $payload))) {
            $headers['alipay-app-auth-token'] = $appAuthToken;
        }

        $rocket->mergePayload(['_headers' => $headers]);

        Logger::info('[Alipay][V3][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidConfigException
     */
    protected function getAuthorizationString(string $tenant, array $config, ?Collection $payload): string
    {
        $timestamp = (new DateTime())->format('Uv');
        $nonce = Str::random(32);

        $content = 'app_id='.($config['app_id'] ?? 'null').','
            .'app_cert_sn='.get_alipay_app_public_cert_sn($tenant, $config).','
            .'timestamp='.$timestamp.','
            .'nonce='.$nonce;

        if (!empty($appAuthToken = get_config_value('app_auth_token', $config, $payload))) {
            $content .= ',appAuthToken='.$appAuthToken;
        }

        return $content;
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function getSignatureContent(string $authorizationString, array $config, ?Collection $payload): string
    {
        $url = get_alipay_url($config, $payload);
        $urlPath = parse_url($url, PHP_URL_PATH);
        $urlQuery = parse_url($url, PHP_URL_QUERY);

        $content = $authorizationString."\n"
            .get_alipay_method($payload)."\n"
            .$urlPath.(empty($urlQuery) ? '' : '?'.$urlQuery)."\n"
            .get_alipay_body($payload)."\n";

        if (!empty($appAuthToken = get_config_value('app_auth_token', $config, $payload))) {
            $content .= $appAuthToken."\n";
        }

        return $content;
    }
}
