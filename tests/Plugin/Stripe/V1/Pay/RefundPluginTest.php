<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\RefundPlugin;
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

    public function testNormalWithPaymentIntent()
    {
        $rocket = new Rocket();
        $rocket->setParams(['payment_intent' => 'pi_test123'])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('v1/refunds', $payload->get('_url'));
    }

    public function testNormalWithCharge()
    {
        $rocket = new Rocket();
        $rocket->setParams(['charge' => 'ch_test789'])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('v1/refunds', $payload->get('_url'));
    }

    public function testNormalWithPaymentIntentInPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection(['payment_intent' => 'pi_test123']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('v1/refunds', $payload->get('_url'));
    }

    public function testMissingPaymentIntentAndCharge()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
