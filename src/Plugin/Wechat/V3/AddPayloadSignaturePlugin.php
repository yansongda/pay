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
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

class AddPayloadSignaturePlugin implements PluginInterface
{
    use WechatTrait;

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

        $config = self::getProviderConfig('wechat', $rocket->getParams());
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
    protected function getSignatureContent(WechatConfig $config, ?Collection $payload, int $timestamp, string $random): string
    {
        $url = self::getWechatUrl($config, $payload);
        $urlPath = parse_url($url, PHP_URL_PATH);
        $urlQuery = parse_url($url, PHP_URL_QUERY);

        return self::getWechatMethod($payload)."\n"
            .$urlPath.(empty($urlQuery) ? '' : '?'.$urlQuery)."\n"
            .$timestamp."\n"
            .$random."\n"
            .self::getWechatBody($payload)."\n";
    }

    /**
     * @throws InvalidConfigException
     */
    protected function getSignature(WechatConfig $config, int $timestamp, string $random, string $contents): string
    {
        $mchPublicCertPath = $config->getMchPublicCertPath();

        if (empty($mchPublicCertPath)) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 缺少微信配置 -- [mch_public_cert_path]');
        }

        $ssl = openssl_x509_parse(CertManager::getPublicCert($mchPublicCertPath));

        if (empty($ssl['serialNumberHex'])) {
            throw new InvalidConfigException(Exception::CONFIG_WECHAT_INVALID, '配置异常: 解析微信配置 [mch_public_cert_path] 出错');
        }

        $auth = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $config->getMchId(),
            $random,
            $timestamp,
            $ssl['serialNumberHex'],
            self::getWechatSign($config, $contents),
        );

        return 'WECHATPAY2-SHA256-RSA2048 '.$auth;
    }
}
