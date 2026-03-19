<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V1\Pay\RefundPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundPluginTest extends TestCase
{
    protected RefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['capture_id' => 'CAP_123'])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertStringContainsString('CAP_123', $payload->get('_url'));
        self::assertStringContainsString('refund', $payload->get('_url'));
    }

    public function testWithAmount()
    {
        $amount = ['currency_code' => 'USD', 'value' => '5.00'];

        $rocket = new Rocket();
        $rocket->setParams(['capture_id' => 'CAP_456'])->setPayload(new Collection(['amount' => $amount]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals($amount, $payload->get('amount'));
    }
}
