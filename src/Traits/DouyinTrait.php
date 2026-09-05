<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Provider\Douyin;
use Yansongda\Supports\Collection;

trait DouyinTrait
{
    use ProviderConfigTrait;

    /**
     * @throws InvalidParamsException
     */
    public static function getDouyinUrl(DouyinConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_DOUYIN_URL_MISSING, '参数异常: 抖音 `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Douyin::URL[$config->getMode()].$url;
    }

    /**
     * @throws InvalidConfigException 应用私钥无效或签名失败
     */
    public static function getDouyinTradeSign(DouyinConfig $config, string $method, string $uri, string $body, ?string $timestamp = null, ?string $nonce = null): string
    {
        $timestamp = $timestamp ?? (string) time();
        $nonce = $nonce ?? bin2hex(random_bytes(8));

        $contents = $method."\n".$uri."\n".$timestamp."\n".$nonce."\n".$body."\n";

        $privateKey = openssl_pkey_get_private($config->getAppPrivateKey());

        if (false === $privateKey) {
            throw new InvalidConfigException(Exception::CONFIG_DOUYIN_INVALID, '配置异常: 抖音应用私钥无效');
        }

        if (!openssl_sign($contents, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new InvalidConfigException(Exception::CONFIG_DOUYIN_INVALID, '配置异常: 抖音应用私钥无效');
        }

        return sprintf('SHA256-RSA2048 appid="%s",nonce_str="%s",timestamp="%s",key_version=1,signature="%s"', $config->getAppId(), $nonce, $timestamp, base64_encode($signature));
    }

    /**
     * @throws InvalidConfigException 平台公钥无效
     * @throws InvalidSignException   签名为空或验签失败
     */
    public static function verifyDouyinTradeSign(ServerRequestInterface $request, DouyinConfig $config): void
    {
        $timestamp = $request->getHeaderLine('Byte-Timestamp');
        $nonce = $request->getHeaderLine('Byte-Nonce-Str');
        $sign = $request->getHeaderLine('Byte-Signature');
        $body = (string) $request->getBody();

        if (empty($timestamp) || empty($nonce) || empty($sign) || empty($body)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY);
        }

        $publicKey = openssl_pkey_get_public($config->getPlatformPublicKey());

        if (false === $publicKey) {
            throw new InvalidConfigException(Exception::CONFIG_DOUYIN_INVALID, '配置异常: 抖音平台公钥无效');
        }

        $contents = $timestamp."\n".$nonce."\n".$body."\n";

        if (1 !== openssl_verify($contents, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证抖音回调签名失败');
        }
    }
}
