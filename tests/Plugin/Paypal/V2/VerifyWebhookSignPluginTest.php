<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V2;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V2\VerifyWebhookSignPlugin;
use Yansongda\Pay\Tests\TestCase;

class VerifyWebhookSignPluginTest extends TestCase
{
    protected VerifyWebhookSignPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new VerifyWebhookSignPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([
            '_verify_url' => 'https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature',
            '_verify_body' => '{"auth_algo":"SHA256withRSA"}',
            '_access_token' => 'test-token-123',
        ]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature', $result->getPayload()->get('_url'));
        self::assertEquals('{"auth_algo":"SHA256withRSA"}', $result->getPayload()->get('_body'));
        self::assertEquals('test-token-123', $result->getPayload()->get('_access_token'));
    }
}
