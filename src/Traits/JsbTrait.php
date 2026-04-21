<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Provider\Jsb;
use Yansongda\Supports\Collection;

trait JsbTrait
{
    use ProviderConfigTrait;

    public static function getJsbUrl(JsbConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload) ?? '';

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Jsb::URL[$config->getMode()];
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verifyJsbSign(JsbConfig $config, string $content, string $sign): void
    {
        if (empty($sign)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 江苏银行签名为空', func_get_args());
        }

        $publicCert = $config->getJsbPublicCertPath();

        if (empty($publicCert)) {
            throw new InvalidConfigException(Exception::CONFIG_JSB_INVALID, '配置异常: 缺少配置参数 -- [jsb_public_cert_path]');
        }

        $result = 1 === openssl_verify(
            $content,
            base64_decode($sign),
            CertManager::getPublicCert($publicCert)
        );

        if (!$result) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证江苏银行签名失败', func_get_args());
        }
    }
}
