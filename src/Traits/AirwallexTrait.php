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
use Yansongda\Pay\Config\AirwallexConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\GetAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Provider\Airwallex;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

trait AirwallexTrait
{
    use ProviderConfigTrait;

    /**
     * @throws InvalidParamsException
     */
    public static function getAirwallexUrl(AirwallexConfig $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_AIRWALLEX_URL_MISSING, '参数错误: Airwallex `_url` 缺失。 请检查是否先运行了 AddRadarPlugin。');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Airwallex::URL[$config->getMode()].$url;
    }

    public static function getAirwallexRequestId(): string
    {
        return Str::uuidV4();
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public static function getAirwallexAccessToken(array $params): string
    {
        /** @var AirwallexConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_AIRWALLEX, $params);

        if (!empty($config->getAccessToken())
            && !empty($config->getAccessTokenExpiry())
            && time() < $config->getAccessTokenExpiry()) {
            return $config->getAccessToken();
        }

        if (empty($config->getClientId()) || empty($config->getApiKey())) {
            throw new InvalidConfigException(Exception::CONFIG_AIRWALLEX_INVALID, '配置错误: Airwallex 配置缺少 [client_id] 或 [api_key]');
        }

        $result = Artful::artful([
            StartPlugin::class,
            GetAccessTokenPlugin::class,
            AddRadarPlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ], $params);

        $token = $result->get('token', '');
        $expiresAt = strtotime($result->get('expires_at', '+30 minutes'));

        $config->setAccessToken($token);
        $config->setAccessTokenExpiry(max(time(), $expiresAt - 60));

        return $token;
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public static function verifyAirwallexWebhookSign(ServerRequestInterface $request, array $params): void
    {
        /** @var AirwallexConfig $config */
        $config = self::getProviderConfig(Pay::PROVIDER_AIRWALLEX, $params);
        $webhookSecret = $config->getWebhookSecret();

        if (empty($webhookSecret)) {
            throw new InvalidConfigException(Exception::CONFIG_AIRWALLEX_INVALID, '配置错误: Airwallex 配置缺少 [webhook_secret]');
        }

        $timestamp = $request->getHeaderLine('x-timestamp');
        $signature = $request->getHeaderLine('x-signature');
        $body = (string) $request->getBody();

        if (empty($signature) || empty($timestamp)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '签名错误: Airwallex webhook 签名请求头为空', ['headers' => $request->getHeaders(), 'body' => $body]);
        }

        $expectedSignature = hash_hmac('sha256', $timestamp.$body, $webhookSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名错误: Airwallex webhook 签名验证失败', ['headers' => $request->getHeaders(), 'body' => $body]);
        }

        if (abs((int) (microtime(true) * 1000) - (int) $timestamp) > 300000) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '签名错误: Airwallex webhook 签名时间戳已过期', ['timestamp' => $timestamp]);
        }
    }
}
