<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
            'amount' => 100,
            'currency' => 'USD',
            'merchant_order_id' => 'order_123',
        ]));

        $result = (new PayPlugin())->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('api/v1/pa/payment_intents/create', $payload->get('_url'));
        self::assertEquals('https://pay.yansongda.cn/airwallex/return', $payload->get('return_url'));
        self::assertNotEmpty($payload->get('request_id'));
    }
}
