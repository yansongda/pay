<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

trait AlipayTrait
{
    use ProviderConfigTrait;

    /**
     * @throws InvalidConfigException 缺少支付宝公钥证书配置
     * @throws InvalidSignException   签名为空或验签失败
     */
    public static function verifyAlipaySign(AlipayConfig $config, string $contents, string $sign): void
    {
        if ('' === $sign) {
            throw new InvalidSignException(Exception::SIGN_EMPTY);
        }

        if (empty($config->getAlipayPublicCertPath())) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [alipay_public_cert_path]');
        }

        $publicCert = CertManager::getPublicCert($config->getAlipayPublicCertPath());
        $publicKey = openssl_pkey_get_public($publicCert);

        if (false === $publicKey || 1 !== openssl_verify($contents, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256)) {
            throw new InvalidSignException(Exception::SIGN_ERROR);
        }
    }

    public static function getAlipayUrl(AlipayConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (is_string($url) && str_starts_with($url, 'http')) {
            return $url;
        }

        return Alipay::URL[$config->getMode()].'/gateway.do?charset=utf-8';
    }

    /**
     * @throws InvalidConfigException 缺少商户私钥配置
     */
    public static function getAlipayPrivateKey(AlipayConfig $config): string
    {
        $privateKey = $config->getAppSecretCert();

        if (empty($privateKey)) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [app_secret_cert]');
        }

        return CertManager::getPrivateCert($privateKey);
    }

    /**
     * 获取支付宝 V3 请求 URL：radar 完整 URL 优先，否则网关 host（沙箱为 V3 专用网关）+ 业务 path.
     */
    public static function getAlipayV3Url(AlipayConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (is_string($url) && str_starts_with($url, 'http')) {
            return $url;
        }

        $base = Pay::MODE_SANDBOX === $config->getMode() ? Alipay::V3_SANDBOX_URL : Alipay::URL[$config->getMode()];

        return $base.($url ?? '');
    }

    /**
     * 生成支付宝 V3 请求 `Authorization` header 值.
     *
     * 待签名组串共 5 行：authString、httpMethod、requestUri（path+query，不含 host）、
     * requestBody（空 body 保留空行）、appAuthToken（缺省时整行缺省）.
     *
     * @see https://opendocs.alipay.com/open-v3/05419m 支付宝支付签名生成算法
     *
     * @throws InvalidConfigException 缺少商户私钥/应用公钥证书配置或证书解析失败
     */
    public static function getAlipayV3Authorization(AlipayConfig $config, string $httpMethod, string $httpRequestUri, string $httpRequestBody = '', ?string $appAuthToken = null): string
    {
        $authString = 'app_id='.$config->getAppId();

        if (empty($appPublicCertPath = $config->getAppPublicCertPath())) {
            throw new InvalidConfigException(Exception::CONFIG_ALIPAY_INVALID, '配置异常: 缺少支付宝配置 -- [app_public_cert_path]');
        }

        $authString .= ',app_cert_sn='.CertManager::alipayGetAppCertSn($appPublicCertPath);

        $authString .= ',nonce='.self::getAlipayV3Uuid().',timestamp='.self::getAlipayV3Timestamp();

        $content = $authString."\n"
            .$httpMethod."\n"
            .$httpRequestUri."\n"
            .('' !== $httpRequestBody ? $httpRequestBody : '')."\n"
            .(null !== $appAuthToken && '' !== $appAuthToken ? $appAuthToken."\n" : '');

        openssl_sign($content, $sign, self::getAlipayPrivateKey($config), OPENSSL_ALGO_SHA256);

        return 'ALIPAY-SHA256withRSA '.$authString.',sign='.base64_encode($sign);
    }

    /**
     * 验证支付宝 V3 时间戳是否在有效期内（5 分钟，13 位毫秒）.
     *
     * @throws InvalidSignException 时间戳已过期
     */
    public static function verifyAlipayV3Timestamp(string $timestamp): void
    {
        if (abs(time() - intdiv((int) $timestamp, 1000)) > 300) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 支付宝 V3 时间戳已过期', ['timestamp' => $timestamp, 'current_time' => time()]);
        }
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function loadAlipayServiceProvider(Rocket $rocket): void
    {
        $params = $rocket->getParams();

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_ALIPAY, $params);
        $serviceProviderId = $config->getServiceProviderId();

        if (Pay::MODE_SERVICE !== $config->getMode()
            || empty($serviceProviderId)) {
            return;
        }

        $rocket->mergeParams([
            'extend_params' => array_merge($params['extend_params'] ?? [], ['sys_service_provider_id' => $serviceProviderId]),
        ]);
    }

    /**
     * 获取当前毫秒时间戳（对齐官方 `getCurrentMilis()` 手法）.
     */
    private static function getAlipayV3Timestamp(): string
    {
        $timeInfo = explode(' ', microtime());

        return sprintf('%d%03d', (int) $timeInfo[1], (int) ((float) $timeInfo[0] * 1000));
    }

    /**
     * 生成请求唯一 ID（UUID v4，加密安全随机源）.
     */
    private static function getAlipayV3Uuid(): string
    {
        return Str::uuidV4();
    }
}
