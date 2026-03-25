<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Bestpay\V1\Pay\RefundPlugin;
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

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([])
            ->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('refund/applyRefund', $result->getPayload()->get('_url'));
        self::assertEquals('POST', $result->getPayload()->get('_method'));
    }
}
