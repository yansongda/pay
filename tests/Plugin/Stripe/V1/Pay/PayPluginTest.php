<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\PayPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([
            'amount' => 1000,
            'currency' => 'usd',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('v1/payment_intents', $payload->get('_url'));
        self::assertEquals(1000, $payload->get('amount'));
        self::assertEquals('usd', $payload->get('currency'));
    }

    public function testMethodAndUrlAreSet()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['amount' => 500, 'currency' => 'eur']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('v1/payment_intents', $result->getPayload()->get('_url'));
    }
}
