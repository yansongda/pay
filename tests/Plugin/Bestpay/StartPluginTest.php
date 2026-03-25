<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Bestpay\V1\StartPlugin;
use Yansongda\Pay\Tests\TestCase;

class StartPluginTest extends TestCase
{
    protected StartPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new StartPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertEquals('bestpay_merchant_no', $payload->get('merchantNo'));
        self::assertEquals('HELIPAY', $payload->get('platform'));
        self::assertEquals('MD5', $payload->get('signType'));
        self::assertEquals(date('YmdHis'), $payload->get('requestTimestamp'));
    }

    public function testParamsFiltering(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_url' => 'some/url', 'productName' => 'Test Product']);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        // Private params starting with _ should be filtered out
        self::assertNull($payload->get('_url'));
        // Regular params should be merged
        self::assertEquals('Test Product', $payload->get('productName'));
    }
}
