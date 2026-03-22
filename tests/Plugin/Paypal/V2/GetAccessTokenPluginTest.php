<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V2;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V2\GetAccessTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class GetAccessTokenPluginTest extends TestCase
{
    protected GetAccessTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GetAccessTokenPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('v1/oauth2/token', $payload->get('_url'));
        self::assertEquals('basic', $payload->get('_auth_type'));
    }
}
