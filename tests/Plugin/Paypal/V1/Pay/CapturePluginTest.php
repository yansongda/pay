<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V1\Pay\CapturePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CapturePluginTest extends TestCase
{
    protected CapturePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CapturePlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['order_id' => 'TEST_ORDER_123'])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertStringContainsString('TEST_ORDER_123', $payload->get('_url'));
        self::assertStringContainsString('capture', $payload->get('_url'));
    }

    public function testEmptyOrderId()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('v2/checkout/orders//capture', $result->getPayload()->get('_url'));
    }
}
