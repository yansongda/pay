<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\CancelPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CancelPluginTest extends TestCase
{
    protected CancelPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CancelPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['payment_intent_id' => 'pi_test123'])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertStringContainsString('pi_test123', $payload->get('_url'));
        self::assertStringContainsString('cancel', $payload->get('_url'));
    }

    public function testUrlContainsCancel()
    {
        $rocket = new Rocket();
        $rocket->setParams(['payment_intent_id' => 'pi_abc'])->setPayload(new Collection([]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('v1/payment_intents/pi_abc/cancel', $result->getPayload()->get('_url'));
    }

    public function testMissingPaymentIntentId()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
