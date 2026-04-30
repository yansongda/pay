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

        return Alipay::URL[$config->getMode()];
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
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function loadAlipayServiceProvider(Rocket $rocket): void
    {
        $params = $rocket->getParams();

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig('alipay', $params);
        $serviceProviderId = $config->getServiceProviderId();

        if (Pay::MODE_SERVICE !== $config->getMode()
            || empty($serviceProviderId)) {
            return;
        }

        $rocket->mergeParams([
            'extend_params' => array_merge($params['extend_params'] ?? [], ['sys_service_provider_id' => $serviceProviderId]),
        ]);
    }
}
