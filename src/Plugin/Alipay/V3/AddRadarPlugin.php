<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use GuzzleHttp\Psr7\Request;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

use function Yansongda\Artful\get_radar_method;
use function Yansongda\Pay\get_alipay_v3_url;
use function Yansongda\Pay\get_private_cert;
use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\get_public_cert;
use function Yansongda\Pay\get_tenant;

class AddRadarPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][AddRadarPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_provider_config('alipay', $params);

        $method = get_radar_method($payload) ?? 'POST';
        $fullUrl = get_alipay_v3_url($config, $payload);
        $body = $payload?->get('_body', '') ?? '';

        $appId = $config['app_id'] ?? '';
        $appCertSn = $this->getAppCertSn(get_tenant($params), $config);
        $nonce = $this->generateNonce();
        $timestamp = (string) (int) (microtime(true) * 1000);

        $authString = "app_id={$appId},app_cert_sn={$appCertSn},nonce={$nonce},timestamp={$timestamp}";

        $parsedUrl = parse_url($fullUrl);
        $signPath = $parsedUrl['path'] ?? '';
        if (!empty($parsedUrl['query'])) {
            $signPath .= '?'.$parsedUrl['query'];
        }

        $signContent = $authString."\n".$method."\n".$signPath."\n".$body."\n";

        $appAuthToken = $payload?->get('app_auth_token', '') ?? '';
        if (!empty($appAuthToken)) {
            $signContent .= $appAuthToken."\n";
        }

        $sign = $this->sign($signContent, $config);

        $headers = [
            'Authorization' => "ALIPAY-SHA256withRSA {$authString},sign={$sign}",
            'Content-Type' => 'application/json; charset=utf-8',
            'User-Agent' => 'yansongda/pay-v3',
            'alipay-request-id' => $this->generateNonce(),
        ];

        if (!empty($appAuthToken)) {
            $headers['alipay-app-auth-token'] = $appAuthToken;
        }

        $rocket->setRadar(new Request(
            $method,
            $fullUrl,
            $headers,
            $body,
        ));

        Logger::info('[Alipay][V3][AddRadarPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function generateNonce(): string
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(6)),
        );
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function getAppCertSn(string $tenant, array $config): string
    {
        if (!empty($config['app_public_cert_sn'])) {
            return $config['app_public_cert_sn'];
        }

        $path = $config['app_public_cert_path'] ?? null;

        if (is_null($path)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [app_public_cert_path]');
        }

        $ssl = openssl_x509_parse(get_public_cert($path));

        if (false === $ssl) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 解析 `app_public_cert_path` 失败');
        }

        $result = $this->getCertSn($ssl['issuer'] ?? [], $ssl['serialNumber'] ?? '');

        Pay::get(ConfigInterface::class)->set('alipay.'.$tenant.'.app_public_cert_sn', $result);

        return $result;
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    protected function sign(string $content, array $config): string
    {
        $privateKey = $config['app_secret_cert'] ?? null;

        if (is_null($privateKey)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [app_secret_cert]');
        }

        openssl_sign($content, $sign, get_private_cert($privateKey), OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    protected function getCertSn(array $issuer, string $serialNumber): string
    {
        return md5($this->array2string(array_reverse($issuer)).$serialNumber);
    }

    protected function array2string(array $array): string
    {
        $string = [];

        foreach ($array as $key => $value) {
            $string[] = $key.'='.$value;
        }

        return implode(',', $string);
    }
}
