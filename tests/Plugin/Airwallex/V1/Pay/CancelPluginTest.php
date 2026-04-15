<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\CancelPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CancelPluginTest extends TestCase
{
    public function testNormal()
    {
        $result = (new CancelPlugin())->assembly(
            (new Rocket())->setPayload(new Collection(['payment_intent_id' => 'int_test123'])),
            fn ($rocket) => $rocket
        );

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('api/v1/pa/payment_intents/int_test123/cancel', $result->getPayload()->get('_url'));
        self::assertNotEmpty($result->getPayload()->get('request_id'));
    }

    public function testUseIdAndCancellationReason()
    {
        $result = (new CancelPlugin())->assembly(
            (new Rocket())->setPayload(new Collection([
                'id' => 'int_test456',
                'request_id' => 'req_test456',
                'cancellation_reason' => 'requested_by_customer',
            ])),
            fn ($rocket) => $rocket
        );

        self::assertEquals('api/v1/pa/payment_intents/int_test456/cancel', $result->getPayload()->get('_url'));
        self::assertEquals('req_test456', $result->getPayload()->get('request_id'));
        self::assertEquals('requested_by_customer', $result->getPayload()->get('cancellation_reason'));
    }
}
