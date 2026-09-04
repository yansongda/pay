<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Paypal\V2\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Paypal\V2\Pay\RefundPlugin;
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

        // capture_id 不应残留在 payload 中，否则会被打进最终请求 body
        self::assertNull($payload->get('capture_id'));
    }

    public function testWithAmount()
    {
        $amount = ['currency_code' => 'USD', 'value' => '5.00'];

        $rocket = new Rocket();
        $rocket->setParams(['capture_id' => 'CAP_456'])->setPayload(new Collection(['amount' => $amount]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload();

        self::assertEquals($amount, $payload->get('amount'));

        // 仅保留白名单内的业务字段
        self::assertEquals([
            '_method' => 'POST',
            '_url' => '/v2/payments/captures/CAP_456/refund',
            'amount' => $amount,
        ], $payload->all());
    }

    public function testMissingCaptureId()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection([]));

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
