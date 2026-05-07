<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Traits;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Config\AirwallexConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Pay\Traits\AirwallexTrait;
use Yansongda\Supports\Collection;

class AirwallexTraitStub
{
    use AirwallexTrait;
}

class AirwallexTraitTest extends TestCase
{
    public function testGetAirwallexRequestId(): void
    {
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            AirwallexTraitStub::getAirwallexRequestId()
        );
    }

    public function testGetAirwallexUrl(): void
    {
        $config = new AirwallexConfig([
            'client_id' => 'airwallex_client_id',
            'api_key' => 'airwallex_api_key',
        ]);
        $sandboxConfig = new AirwallexConfig([
            'client_id' => 'airwallex_client_id',
            'api_key' => 'airwallex_api_key',
            'mode' => Pay::MODE_SANDBOX,
        ]);

        self::assertSame('https://yansongda.cn', AirwallexTraitStub::getAirwallexUrl($config, new Collection(['_url' => 'https://yansongda.cn'])));
        self::assertSame('https://api.airwallex.com/api/v1/authentication/login', AirwallexTraitStub::getAirwallexUrl($config, new Collection(['_url' => 'api/v1/authentication/login'])));
        self::assertSame('https://api-demo.airwallex.com/api/v1/authentication/login', AirwallexTraitStub::getAirwallexUrl($sandboxConfig, new Collection(['_url' => 'api/v1/authentication/login'])));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_AIRWALLEX_URL_MISSING);
        AirwallexTraitStub::getAirwallexUrl($config, new Collection([]));
    }

    public function testGetAirwallexAccessTokenFromCache(): void
    {
        /** @var AirwallexConfig $config */
        $config = Pay::get(ConfigInterface::class)->get('airwallex.default');
        $config->setAccessToken('cached_airwallex_token');
        $config->setAccessTokenExpiry(time() + 3600);

        self::assertSame('cached_airwallex_token', AirwallexTraitStub::getAirwallexAccessToken([]));
    }

    public function testGetAirwallexAccessTokenMissingCredentials(): void
    {
        /** @var AirwallexConfig $config */
        $config = Pay::get(ConfigInterface::class)->get('airwallex.default');
        $config->setClientId('');

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_AIRWALLEX_INVALID);

        AirwallexTraitStub::getAirwallexAccessToken([]);
    }

    public function testVerifyAirwallexWebhookSignEmptySignature(): void
    {
        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        AirwallexTraitStub::verifyAirwallexWebhookSign($request, []);
    }

    public function testVerifyAirwallexWebhookSign(): void
    {
        $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
        $timestamp = (string) ((int) (microtime(true) * 1000));
        $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ], $body);

        AirwallexTraitStub::verifyAirwallexWebhookSign($request, []);

        self::assertTrue(true);
    }

    public function testVerifyAirwallexWebhookSignExpiredTimestamp(): void
    {
        $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
        $timestamp = (string) (((int) (microtime(true) * 1000)) - 301000);
        $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/airwallex/notify', [
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ], $body);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        AirwallexTraitStub::verifyAirwallexWebhookSign($request, []);
    }
}
