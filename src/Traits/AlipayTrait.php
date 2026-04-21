<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Supports\Collection;

trait AlipayTrait
{
    use ProviderConfigTrait;

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
        $url = self::getRadarUrl($config->toArray(), $payload);

        if (is_string($url) && str_starts_with($url, 'http')) {
            return $url;
        }

        return Alipay::URL[$config->getMode()];
    }
}
