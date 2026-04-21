<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Config\PaypalConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Paypal\V2\AddRadarPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\GetAccessTokenPlugin;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;
use Yansongda\Pay\Plugin\Paypal\V2\VerifyWebhookSignPlugin;
use Yansongda\Pay\Provider\Paypal;
use Yansongda\Supports\Collection;

trait PaypalTrait
{
    use ProviderConfigTrait;

    public static function getPaypalUrl(PaypalConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_PAYPAL_URL_MISSING, '参数异常: PayPal `_url` 参数缺失：你可能用错插件顺序，应该先使用 `业务插件`');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Paypal::URL[$config->getMode()].$url;
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public static function getPaypalAccessToken(array $params): string
    {
        /** @var PaypalConfig $config */
        $config = self::getProviderConfig('paypal', $params);

        if (!empty($config->getAccessToken())
            && !empty($config->getAccessTokenExpiry())
            && time() < $config->getAccessTokenExpiry()) {
            return $config->getAccessToken();
        }

        if (empty($config->getClientId()) || empty($config->getAppSecret())) {
            throw new InvalidConfigException(Exception::CONFIG_PAYPAL_INVALID, '配置异常: 缺少 PayPal 配置 -- [client_id] or [app_secret]');
        }

        $result = Artful::artful([
            StartPlugin::class,
            GetAccessTokenPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $params);

        $token = $result->get('access_token', '');
        $expiresIn = $result->get('expires_in', 32400);

        $config->setAccessToken($token);
        $config->setAccessTokenExpiry(time() + $expiresIn - 60);

        return $token;
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public static function verifyPaypalWebhookSign(ServerRequestInterface $request, array $params): void
    {
        /** @var PaypalConfig $config */
        $config = self::getProviderConfig('paypal', $params);

        $webhookId = $config->getWebhookId();

        if (empty($webhookId)) {
            throw new InvalidConfigException(Exception::CONFIG_PAYPAL_INVALID, '配置异常: 缺少 PayPal 配置 -- [webhook_id]');
        }

        $transmissionId = $request->getHeaderLine('PAYPAL-TRANSMISSION-ID');
        $transmissionTime = $request->getHeaderLine('PAYPAL-TRANSMISSION-TIME');
        $transmissionSig = $request->getHeaderLine('PAYPAL-TRANSMISSION-SIG');
        $certUrl = $request->getHeaderLine('PAYPAL-CERT-URL');
        $authAlgo = $request->getHeaderLine('PAYPAL-AUTH-ALGO');
        $body = (string) $request->getBody();

        if (empty($transmissionId) || empty($transmissionSig)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名异常: PayPal 回调签名为空', ['headers' => $request->getHeaders(), 'body' => $body]);
        }

        $webhookEvent = json_decode($body, true);

        $verifyPayload = [
            'auth_algo' => $authAlgo,
            'cert_url' => $certUrl,
            'transmission_id' => $transmissionId,
            'transmission_sig' => $transmissionSig,
            'transmission_time' => $transmissionTime,
            'webhook_id' => $webhookId,
            'webhook_event' => $webhookEvent,
        ];

        $token = self::getPaypalAccessToken($params);
        $url = Paypal::URL[$config->getMode()].'v1/notifications/verify-webhook-signature';

        $result = Artful::artful([
            StartPlugin::class,
            VerifyWebhookSignPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], array_merge($params, [
            '_verify_url' => $url,
            '_verify_body' => json_encode($verifyPayload),
            '_access_token' => $token,
        ]));

        $status = $result->get('verification_status', '');

        if ('SUCCESS' !== $status) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名异常: 验证 PayPal 回调签名失败', ['headers' => $request->getHeaders(), 'body' => $body, 'verification_status' => $status]);
        }
    }
}
