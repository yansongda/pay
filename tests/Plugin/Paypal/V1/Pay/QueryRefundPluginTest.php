<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V1\Pay\QueryRefundPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryRefundPluginTest extends TestCase
{
    protected QueryRefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryRefundPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['refund_id' => 'REF_789'])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertStringContainsString('REF_789', $payload->get('_url'));
    }

    public function testEmptyRefundId()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('v2/payments/refunds/', $result->getPayload()->get('_url'));
    }
}
