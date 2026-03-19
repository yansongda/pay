<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V1;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V1\AddRadarPlugin;
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

    public function testBearerAuth()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'v2/checkout/orders',
            '_body' => '{"intent":"CAPTURE"}',
            '_access_token' => 'test_token_abc',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertEquals('yansongda/pay-v3', $radar->getHeaderLine('User-Agent'));
        self::assertEquals('application/json; charset=utf-8', $radar->getHeaderLine('Content-Type'));
        self::assertStringStartsWith('Bearer ', $radar->getHeaderLine('Authorization'));
        self::assertStringContainsString('test_token_abc', $radar->getHeaderLine('Authorization'));
        self::assertEquals('POST', $radar->getMethod());
        self::assertStringContainsString('v2/checkout/orders', (string) $radar->getUri());
    }

    public function testBasicAuth()
    {
        $payload = new Collection([
            '_method' => 'POST',
            '_url' => 'v1/oauth2/token',
            '_auth_type' => 'basic',
        ]);

        $rocket = (new Rocket())->setParams([])->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $radar = $result->getRadar();

        self::assertStringStartsWith('Basic ', $radar->getHeaderLine('Authorization'));
        self::assertEquals('application/x-www-form-urlencoded', $radar->getHeaderLine('Content-Type'));
        self::assertEquals('grant_type=client_credentials', (string) $radar->getBody());
    }
}
