<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Stripe\V1\AddRadarPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddRadarPluginTest extends TestCase
{
    protected AddRadarPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddRadarPlugin();
    }

    public function testPostRequest()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/v1/payment_intents',
            'amount' => 1000,
            'currency' => 'usd',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('yansongda/pay-v3', $radar->getHeaderLine('User-Agent'));
        self::assertEquals('application/x-www-form-urlencoded', $radar->getHeaderLine('Content-Type'));
        self::assertEquals('Bearer sk_test_stripe_secret', $radar->getHeaderLine('Authorization'));
        self::assertEquals('POST', $radar->getMethod());
        self::assertStringContainsString('v1/payment_intents', (string) $radar->getUri());
    }

    public function testGetRequest()
    {
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => '/v1/payment_intents/pi_test123',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('GET', $radar->getMethod());
        self::assertEquals('Bearer sk_test_stripe_secret', $radar->getHeaderLine('Authorization'));
        self::assertEmpty($radar->getHeaderLine('Content-Type'));
        self::assertEquals('', (string) $radar->getBody());
    }

    public function testGetRequestWithQueryParams()
    {
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => '/v1/payment_intents/pi_test123',
            'expand' => ['charges'],
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('GET', $radar->getMethod());
        self::assertStringContainsString('expand', (string) $radar->getUri());
    }

    public function testPostBodyIsFormEncoded()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/v1/refunds',
            'payment_intent' => 'pi_test123',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        $body = (string) $radar->getBody();
        self::assertStringContainsString('payment_intent=pi_test123', $body);
    }

    public function testCustomHeaders()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/v1/payment_intents',
            'amount' => 1000,
            'currency' => 'usd',
            '_headers' => [
                'Idempotency-Key' => 'idempotency_key_test',
                'Stripe-Version' => '2024-06-20',
            ],
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('idempotency_key_test', $radar->getHeaderLine('Idempotency-Key'));
        self::assertEquals('2024-06-20', $radar->getHeaderLine('Stripe-Version'));
        // 默认头不被覆盖
        self::assertEquals('Bearer sk_test_stripe_secret', $radar->getHeaderLine('Authorization'));
        // `_headers` 属于内部参数，不能残留在请求 body/query 中
        self::assertStringNotContainsString('_headers', (string) $radar->getBody());
        self::assertStringNotContainsString('_headers', (string) $radar->getUri());
    }

    public function testCustomHeadersNotArrayIgnored()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/v1/payment_intents',
            'amount' => 1000,
            'currency' => 'usd',
            '_headers' => 'invalid',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('Bearer sk_test_stripe_secret', $radar->getHeaderLine('Authorization'));
        self::assertFalse($radar->hasHeader('Idempotency-Key'));
    }
}
