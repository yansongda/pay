<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\AddRadarPlugin;
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

    public function testLoginRequest()
    {
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => 'api/v1/authentication/login',
            '_auth_type' => 'client',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('airwallex_client_id', $radar->getHeaderLine('x-client-id'));
        self::assertEquals('airwallex_api_key', $radar->getHeaderLine('x-api-key'));
        self::assertEquals('2024-06-14', $radar->getHeaderLine('x-api-version'));
    }

    public function testBearerRequest()
    {
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
            '_method' => 'GET',
            '_url' => 'api/v1/pa/payment_intents/int_test123',
            '_access_token' => 'airwallex_access_token',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('Bearer airwallex_access_token', $radar->getHeaderLine('Authorization'));
        self::assertStringContainsString('api-demo.airwallex.com', (string) $radar->getUri());
    }

    public function testOnBehalfOfAndBody()
    {
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
            '_method' => 'POST',
            '_url' => 'api/v1/pa/payment_intents/create',
            '_access_token' => 'airwallex_access_token',
            '_on_behalf_of' => 'acct_123',
            '_body' => '{"amount":100}',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $radar = $result->getRadar();

        self::assertEquals('acct_123', $radar->getHeaderLine('x-on-behalf-of'));
        self::assertEquals('{"amount":100}', (string) $radar->getBody());
    }
}
