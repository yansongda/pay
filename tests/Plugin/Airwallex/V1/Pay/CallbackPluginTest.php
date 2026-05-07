<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1\Pay;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;

class CallbackPluginTest extends TestCase
{
    protected CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testMissingRequestThrowsException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

        $this->plugin->assembly((new Rocket())->setParams([]), fn ($rocket) => $rocket);
    }

    public function testInvalidJsonBodyThrowsException()
    {
        $body = 'not-valid-json';
        $timestamp = (string) ((int) (microtime(true) * 1000));
        $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
        $request = new ServerRequest('POST', 'https://example.com', [
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ], $body);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_AIRWALLEX_BODY_INVALID);

        $this->plugin->assembly((new Rocket())->setParams(['_request' => $request, '_params' => []]), fn ($rocket) => $rocket);
    }

    public function testNormalCallback()
    {
        $body = json_encode([
            'id' => 'evt_test123',
            'name' => 'payment_intent.succeeded',
        ]);
        $timestamp = (string) ((int) (microtime(true) * 1000));
        $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_webhook_secret');
        $request = new ServerRequest('POST', 'https://example.com', [
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ], $body);

        $result = $this->plugin->assembly((new Rocket())->setParams(['_request' => $request, '_params' => []]), fn ($rocket) => $rocket);

        self::assertEquals('evt_test123', $result->getPayload()->get('id'));
        self::assertEquals('payment_intent.succeeded', $result->getDestination()->get('name'));
    }

    public function testCallbackUsesTenantConfig()
    {
        $body = json_encode([
            'id' => 'evt_secondary',
            'name' => 'payment_intent.succeeded',
        ]);
        $timestamp = (string) ((int) (microtime(true) * 1000));
        $signature = hash_hmac('sha256', $timestamp.$body, 'airwallex_secondary_webhook_secret');
        $request = new ServerRequest('POST', 'https://example.com', [
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
        ], $body);

        $result = $this->plugin->assembly((new Rocket())->setParams(['_request' => $request, '_params' => ['_config' => 'secondary']]), fn ($rocket) => $rocket);

        self::assertEquals('evt_secondary', $result->getPayload()->get('id'));
        self::assertEquals('payment_intent.succeeded', $result->getDestination()->get('name'));
    }
}
