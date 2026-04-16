<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\GetAccessTokenPlugin;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Pay\Provider\Airwallex;
use Yansongda\Supports\Collection;

trait AirwallexTrait
{
    use ProviderConfigTrait;

    /**
     * @throws InvalidParamsException
     */
    public static function getAirwallexUrl(array $config, ?Collection $payload): string
    {
        $url = self::getRadarUrl($config, $payload);

        if (empty($url)) {
            throw new InvalidParamsException(Exception::PARAMS_AIRWALLEX_URL_MISSING, '鍙傛暟寮傚父: Airwallex `_url` 鍙傛暟缂哄け锛氫綘鍙兘鐢ㄩ敊鎻掍欢椤哄簭锛屽簲璇ュ厛浣跨敤 `涓氬姟鎻掍欢`');
        }

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return Airwallex::URL[$config['mode'] ?? Pay::MODE_NORMAL].$url;
    }

    public static function getAirwallexRequestId(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0F) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public static function getAirwallexAccessToken(array $params): string
    {
        $config = self::getProviderConfig('airwallex', $params);

        if (!empty($config['_access_token'])
            && !empty($config['_access_token_expiry'])
            && time() < (int) $config['_access_token_expiry']) {
            return $config['_access_token'];
        }

        if (empty($config['client_id']) || empty($config['api_key'])) {
            throw new InvalidConfigException(Exception::CONFIG_AIRWALLEX_INVALID, '閰嶇疆寮傚父: 缂哄皯 Airwallex 閰嶇疆 -- [client_id] or [api_key]');
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

        Pay::get(ConfigInterface::class)->set(
            'airwallex.'.self::getTenant($params).'._access_token',
            $token
        );
        Pay::get(ConfigInterface::class)->set(
            'airwallex.'.self::getTenant($params).'._access_token_expiry',
            max(time(), $expiresAt - 60)
        );

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
        $config = self::getProviderConfig('airwallex', $params);
        $webhookSecret = $config['webhook_secret'] ?? null;

        if (empty($webhookSecret)) {
            throw new InvalidConfigException(Exception::CONFIG_AIRWALLEX_INVALID, '閰嶇疆寮傚父: 缂哄皯 Airwallex 閰嶇疆 -- [webhook_secret]');
        }

        $timestamp = $request->getHeaderLine('x-timestamp');
        $signature = $request->getHeaderLine('x-signature');
        $body = (string) $request->getBody();

        if (empty($signature) || empty($timestamp)) {
            throw new InvalidSignException(Exception::SIGN_EMPTY, '绛惧悕寮傚父: Airwallex 鍥炶皟绛惧悕涓虹┖', ['headers' => $request->getHeaders(), 'body' => $body]);
        }

        $expectedSignature = hash_hmac('sha256', $timestamp.$body, $webhookSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new InvalidSignException(Exception::SIGN_ERROR, '绛惧悕寮傚父: 楠岃瘉 Airwallex 鍥炶皟绛惧悕澶辫触', ['headers' => $request->getHeaders(), 'body' => $body]);
        }
    }
}
