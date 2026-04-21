<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Provider\Stripe;
use Yansongda\Supports\Collection;

trait StripeTrait
{
    use ProviderConfigTrait;

    /**
     * @throws InvalidParamsException
     */
    public static function getStripeUrl(StripeConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_STRIPE_URL_MISSING, '参数异常: Stripe `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Stripe::URL[$config->getMode()].$url;
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public static function verifyStripeWebhookSign(ServerRequestInterface $request, array $params): void
    {
        /** @var StripeConfig $config */
        $config = static::getProviderConfig('stripe', $params);
        $webhookSecret = $config->getWebhookSecret();

        if (empty($webhookSecret)) {
            throw new InvalidConfigException(Exception::CONFIG_STRIPE_INVALID, '配置异常: 缺少 Stripe 配置 -- [webhook_secret]');
        }

        $signatureHeader = $request->getHeaderLine('Stripe-Signature');
        $body = (string) $request->getBody();

        if (empty($signatureHeader)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: Stripe 回调签名为空', ['headers' => $request->getHeaders(), 'body' => $body]);
        }

        $parts = explode(',', $signatureHeader);
        $timestamp = null;
        $signatures = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ('' === $part) {
                continue;
            }

            $pair = explode('=', $part, 2);

            if (2 !== count($pair)) {
                continue;
            }

            [$key, $value] = $pair;
            $key = trim($key);
            $value = trim($value);

            if ('t' === $key) {
                $timestamp = $value;
            } elseif ('v1' === $key) {
                $signatures[] = $value;
            }
        }

        if (empty($timestamp) || empty($signatures)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Stripe 回调签名格式错误', ['headers' => $request->getHeaders(), 'body' => $body]);
        }

        if (abs(time() - (int) $timestamp) > 300) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: Stripe 回调签名时间戳超时', ['timestamp' => $timestamp]);
        }

        $signedPayload = $timestamp.'.'.$body;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return;
            }
        }

        throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证 Stripe 回调签名失败', ['headers' => $request->getHeaders(), 'body' => $body]);
    }
}
