<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Config\AllinpayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Provider\Allinpay;
use Yansongda\Supports\Collection;

trait AllinpayTrait
{
    use ProviderConfigTrait;

    /**
     * @throws InvalidParamsException
     */
    public static function getAllinpayUrl(AllinpayConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_ALLINPAY_URL_MISSING, '参数异常: 通联支付 `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Allinpay::URL[$config->getMode()].$url;
    }

    /**
     * 将参数按 key 字典序拼成 k=v&k=v（跳过空值、数组与 sign），与通联 apiweb 签名规则一致.
     *
     * @param array<string, mixed>|Collection $payload
     */
    public static function getAllinpaySignContent(array|Collection $payload): string
    {
        $payload = Collection::wrap($payload);
        $payload->forget('sign');

        $result = [];

        foreach ($payload->sortKeys() as $key => $value) {
            if (str_starts_with((string) $key, '_') || '' === $value || null === $value || is_array($value)) {
                continue;
            }

            $result[] = $key.'='.$value;
        }

        return implode('&', $result);
    }

    /**
     * @param array<string, mixed>|Collection $payload
     *
     * @throws InvalidConfigException
     */
    public static function getAllinpaySign(AllinpayConfig $config, array|Collection $payload): string
    {
        $privateKey = $config->getMchSecretKey();

        if (empty($privateKey)) {
            throw new InvalidConfigException(Exception::CONFIG_ALLINPAY_INVALID, '配置异常: 缺少配置参数 -- [mch_secret_key]');
        }

        $content = self::getAllinpaySignContent($payload);
        $sign = '';

        openssl_sign($content, $sign, CertManager::getPrivateCert($privateKey), OPENSSL_ALGO_SHA1);

        return base64_encode($sign);
    }

    /**
     * @param array<string, mixed>|Collection $payload
     *
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function verifyAllinpaySign(AllinpayConfig $config, array|Collection $payload): void
    {
        $payload = Collection::wrap($payload);
        $sign = (string) $payload->get('sign', '');

        if (empty($sign)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: 通联支付签名为空', func_get_args());
        }

        $publicKey = $config->getAllinpayPublicKey();

        if (empty($publicKey)) {
            throw new InvalidConfigException(Exception::CONFIG_ALLINPAY_INVALID, '配置异常: 缺少配置参数 -- [allinpay_public_key]');
        }

        $content = self::getAllinpaySignContent($payload);

        $result = 1 === openssl_verify(
            $content,
            base64_decode($sign),
            CertManager::getPublicCert($publicKey),
            OPENSSL_ALGO_SHA1
        );

        if (!$result) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证通联支付签名失败', func_get_args());
        }
    }
}
