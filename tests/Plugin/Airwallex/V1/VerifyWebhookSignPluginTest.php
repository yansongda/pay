<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Airwallex\V1\VerifyWebhookSignPlugin;
use Yansongda\Pay\Tests\TestCase;

use function Yansongda\Pay\verify_airwallex_webhook_sign;

class VerifyWebhookSignPluginTest extends TestCase
{
    public function testMissingWebhookSecretThrowsException()
    {
        $request = new ServerRequest('POST', 'https://example.com', [], '{}');

        self::expectException(InvalidConfigException::class);
        self::expectExceptionCode(Exception::CONFIG_AIRWALLEX_INVALID);

        verify_airwallex_webhook_sign($request, ['_config' => 'no_webhook_secret']);
    }

    public function testMissingSignatureThrowsException()
    {
        $request = new ServerRequest('POST', 'https://example.com', [], '{}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        verify_airwallex_webhook_sign($request, []);
    }

    public function testWrongSignatureThrowsException()
    {
        $request = new ServerRequest('POST', 'https://example.com', [
            'x-timestamp' => '1710000000000',
            'x-signature' => 'wrong-signature',
        ], '{"id":"evt_test"}');

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        verify_airwallex_webhook_sign($request, []);
    }

    public function testValidSignaturePasses()
    {
        $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
        $timestamp = '1710000000000';
        $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
        $request = new ServerRequest('POST', 'https://example.com', [
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ], $body);

        verify_airwallex_webhook_sign($request, []);

        self::assertTrue(true);
    }

    public function testPluginUsesDestinationOriginRequest()
    {
        $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
        $timestamp = '1710000000000';
        $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
        $request = new ServerRequest('POST', 'https://example.com', [
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ], $body);

        $result = (new VerifyWebhookSignPlugin())->assembly(
            (new Rocket())->setParams([])->setDestinationOrigin($request),
            fn ($rocket) => $rocket
        );

        self::assertInstanceOf(Rocket::class, $result);
    }

    public function testPluginMissingRequestThrowsException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

        (new VerifyWebhookSignPlugin())->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
    }
}
