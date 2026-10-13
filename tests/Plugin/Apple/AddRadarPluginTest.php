<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Apple;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Apple\AddRadarPlugin;
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

    public function testNormalGetWithSandboxUrl(): void
    {
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => '/inApps/v1/transactions/123',
            '_no_jwt' => true,
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('GET', $radar->getMethod());
        self::assertEquals('https://api.storekit-sandbox.apple.com/inApps/v1/transactions/123', (string) $radar->getUri());
        self::assertEquals('application/json', $radar->getHeaderLine('Content-Type'));
    }

    public function testAuthorizationWithJwt(): void
    {
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => '/inApps/v1/transactions/123',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        $authorization = $radar->getHeaderLine('Authorization');

        self::assertStringStartsWith('Bearer ', $authorization);
        self::assertCount(3, explode('.', substr($authorization, 7)));
    }

    public function testNoJwtWhenNoJwtFlag(): void
    {
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => '/inApps/v1/transactions/123',
            '_no_jwt' => true,
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertFalse($radar->hasHeader('Authorization'));
    }

    public function testCustomHeadersOverrideContentType(): void
    {
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => '/inApps/v1/transactions/123',
            '_no_jwt' => true,
            '_headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('application/x-www-form-urlencoded', $radar->getHeaderLine('Content-Type'));
    }

    public function testMethodAndBodyPassthrough(): void
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => '/inApps/v1/subscriptions/123',
            '_body' => '{"foo":"bar"}',
            '_no_jwt' => true,
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals('{"foo":"bar"}', (string) $radar->getBody());
    }

    public function testHttpUrlPassthrough(): void
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'https://apple-pay-gateway.apple.com/paymentservices/startSession',
            '_body' => '{"merchantIdentifier":"merchant.com.yansongda.pay"}',
            '_no_jwt' => true,
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('https://apple-pay-gateway.apple.com/paymentservices/startSession', (string) $radar->getUri());
    }

    public function testQueryAppendedWithAmpersandWhenUrlHasQuery(): void
    {
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => '/inApps/v1/subscriptions/123?status=1&status=4',
            '_query' => ['revision' => 'abc'],
            '_no_jwt' => true,
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals(
            'https://api.storekit-sandbox.apple.com/inApps/v1/subscriptions/123?status=1&status=4&revision=abc',
            (string) $radar->getUri()
        );
    }

    public function testQueryAppendedWithQuestionMarkWhenUrlHasNoQuery(): void
    {
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => '/inApps/v1/transactions/123',
            '_query' => ['revision' => 'abc'],
            '_no_jwt' => true,
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals(
            'https://api.storekit-sandbox.apple.com/inApps/v1/transactions/123?revision=abc',
            (string) $radar->getUri()
        );
    }

    public function testQueryFiltersNullValues(): void
    {
        // filter_params 对齐 Stripe：null 值不进入最终 query string
        $payload = new Collection([
            '_method' => 'GET',
            '_url' => '/inApps/v2/history/123',
            '_query' => ['revision' => 'abc', 'startDate' => null, 'endDate' => null],
            '_no_jwt' => true,
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals(
            'https://api.storekit-sandbox.apple.com/inApps/v2/history/123?revision=abc',
            (string) $radar->getUri()
        );
    }
}
