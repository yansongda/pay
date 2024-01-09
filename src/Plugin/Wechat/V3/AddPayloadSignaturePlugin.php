<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3;

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

use function Yansongda\Pay\get_public_cert;
use function Yansongda\Pay\get_wechat_body;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_method;
use function Yansongda\Pay\get_wechat_sign;
use function Yansongda\Pay\get_wechat_url;

class AddPayloadSignaturePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws Throwable                随机数生成失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][AddPayloadSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();

        $timestamp = time();
        $random = Str::random(32);
        $signContent = $this->getSignatureContent($config, $payload, $timestamp, $random);
        $signature = $this->getSignature($config, $timestamp, $random, $signContent);

        $rocket->mergePayload(['_authorization' => $signature]);

        Logger::info('[Wechat][V3][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getSignatureContent(array $config, ?Collection $payload, int $timestamp, string $random): string
    {
        $url = get_wechat_url($config, $payload);
        $urlPath = parse_url($url, PHP_URL_PATH);
        $urlQuery = parse_url($url, PHP_URL_QUERY);

        return get_wechat_method($payload)."\n".
            $urlPath.(empty($urlQuery) ? '' : '?'.$urlQuery)."\n".
            $timestamp."\n".
            $random."\n".
            get_wechat_body($payload)."\n";
    }

    /**
     * @throws InvalidConfigException
     */
    protected function getSignature(array $config, int $timestamp, string $random, string $contents): string
    {
        $mchPublicCertPath = $config['mch_public_cert_path'] ?? null;

        if (empty($mchPublicCertPath)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_public_cert_path]');
        }

        $ssl = openssl_x509_parse(get_public_cert($mchPublicCertPath));

        if (empty($ssl['serialNumberHex'])) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 解析微信配置 [mch_public_cert_path] 出错');
        }

        $auth = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $config['mch_id'] ?? '',
            $random,
            $timestamp,
            $ssl['serialNumberHex'],
            get_wechat_sign($config, $contents),
        );

        return 'WECHATPAY2-SHA256-RSA2048 '.$auth;
    }
}
