<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V2;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V2\ObtainAccessTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class ObtainAccessTokenPluginTest extends TestCase
{
    protected ObtainAccessTokenPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new ObtainAccessTokenPlugin();
    }

    public function testExternalAccessToken()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_access_token' => 'external_token_123']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('external_token_123', $result->getPayload()->get('_access_token'));
    }
}
