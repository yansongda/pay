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
use Yansongda\Pay\Traits\AirwallexTrait;

uses(VerifyWebhookSignPluginTestHelpers::class);

it('missing webhook secret throws exception', function () {
    $request = new ServerRequest('POST', 'https://example.com', [], '{}');

    $this->expectException(InvalidConfigException::class);
    $this->expectExceptionCode(Exception::CONFIG_AIRWALLEX_INVALID);

    $this->verifyWebhookSign($request, ['_config' => 'no_webhook_secret']);
});

it('missing signature throws exception', function () {
    $request = new ServerRequest('POST', 'https://example.com', [], '{}');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_EMPTY);

    $this->verifyWebhookSign($request, []);
});

it('wrong signature throws exception', function () {
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => '1710000000000',
        'x-signature' => 'wrong-signature',
    ], '{"id":"evt_test"}');

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    $this->verifyWebhookSign($request, []);
});

it('expired timestamp throws exception', function () {
    $body = '{"id":"evt_test"}';
    $timestamp = (string) (((int) (microtime(true) * 1000)) - 301000);
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $this->expectException(InvalidSignException::class);
    $this->expectExceptionCode(Exception::SIGN_ERROR);

    $this->verifyWebhookSign($request, []);
});

it('valid signature passes', function () {
    $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $this->verifyWebhookSign($request, []);

    expect(true)->toBeTrue();
});

it('plugin uses destination origin request', function () {
    $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $result = (new VerifyWebhookSignPlugin())->assembly(
        (new Rocket())->setParams([])->setDestinationOrigin($request),
        fn ($rocket) => $rocket
    );

    expect($result)->toBeInstanceOf(Rocket::class);
});

it('plugin uses tenant config', function () {
    $body = '{"id":"evt_test","name":"payment_intent.succeeded"}';
    $timestamp = (string) ((int) (microtime(true) * 1000));
    $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_secondary_webhook_secret');
    $request = new ServerRequest('POST', 'https://example.com', [
        'x-timestamp' => $timestamp,
        'x-signature' => $signature,
    ], $body);

    $result = (new VerifyWebhookSignPlugin())->assembly(
        (new Rocket())->setParams(['_config' => 'secondary'])->setDestinationOrigin($request),
        fn ($rocket) => $rocket
    );

    expect($result)->toBeInstanceOf(Rocket::class);
});

it('plugin missing request throws exception', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

    (new VerifyWebhookSignPlugin())->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
});

trait VerifyWebhookSignPluginTestHelpers
{
    protected static function verifyWebhookSign(ServerRequest $request, array $params = []): void
    {
        $verifier = new class() {
            use AirwallexTrait;
        };
    
        $verifier::verifyAirwallexWebhookSign($request, $params);
    }
}
