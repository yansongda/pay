<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\get_radar_body;

trait UnipayTrait
{
    use ProviderConfigTrait;

    /**
     * @throws InvalidParamsException
     */
    public static function getUnipayUrl(UnipayConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_UNIPAY_URL_MISSING, '参数异常: 银联 `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Unipay::URL[$config->getMode()].$url;
    }

    /**
     * @throws InvalidParamsException
     */
    public static function getUnipayBody(?Collection $payload): string
    {
        $body = get_radar_body($payload);

        if (is_null($body)) {
            throw new InvalidParamsException(Exception::PARAMS_UNIPAY_BODY_MISSING, '参数异常: 银联 `_body` 参数缺失：你可能用错插件顺序，应该先使用 `AddPayloadBodyPlugin`');
        }

        return $body;
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verifyUnipaySign(UnipayConfig $config, string $contents, string $sign, ?string $signPublicKeyCert = null): void
    {
        if (empty($sign)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 银联签名为空', func_get_args());
        }

        if (empty($signPublicKeyCert) && empty($public = $config->getUnipayPublicCertPath())) {
            throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常： 缺少银联配置 -- [unipay_public_cert_path]');
        }

        $result = 1 === openssl_verify(
            hash('sha256', $contents),
            base64_decode($sign),
            CertManager::getPublicCert($signPublicKeyCert ?? $public),
            'sha256'
        );

        if (!$result) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证银联签名失败', func_get_args());
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public static function getUnipaySignQra(UnipayConfig $config, array $payload): string
    {
        $key = $config->getMchSecretKey();

        if (empty($key)) {
            throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 缺少银联配置 -- [mch_secret_key]');
        }

        ksort($payload);

        $buff = '';

        foreach ($payload as $k => $v) {
            $buff .= ('sign' != $k && '' != $v && !is_array($v)) ? $k.'='.$v.'&' : '';
        }

        return strtoupper(md5($buff.'key='.$key));
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verifyUnipaySignQra(UnipayConfig $config, array $destination): void
    {
        $sign = $destination['sign'] ?? null;

        if (empty($sign)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 银联签名为空', $destination);
        }

        $key = $config->getMchSecretKey();

        if (empty($key)) {
            throw new InvalidConfigException(Exception::CONFIG_UNIPAY_INVALID, '配置异常: 缺少银联配置 -- [mch_secret_key]');
        }

        if (self::getUnipaySignQra($config, $destination) !== $sign) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证银联签名失败', $destination);
        }
    }
}
