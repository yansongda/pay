<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\RefundPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundPluginTest extends TestCase
{
    public function testMissingPaymentIntentId()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        (new RefundPlugin())->assembly((new Rocket())->setPayload(new Collection([])), fn ($rocket) => $rocket);
    }

    public function testNormal()
    {
        $result = (new RefundPlugin())->assembly(
            (new Rocket())->setPayload(new Collection([
                'payment_intent_id' => 'int_test123',
                'amount' => 66.6,
            ])),
            fn ($rocket) => $rocket
        );

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('api/v1/pa/refunds/create', $result->getPayload()->get('_url'));
        self::assertEquals('int_test123', $result->getPayload()->get('payment_intent_id'));
    }

    public function testUseIdAndOptionalFields()
    {
        $result = (new RefundPlugin())->assembly(
            (new Rocket())->setPayload(new Collection([
                'id' => 'int_test456',
                'request_id' => 'req_refund_456',
                'reason' => 'requested_by_customer',
                'metadata' => ['biz_order_id' => 'biz_123'],
            ])),
            fn ($rocket) => $rocket
        );

        self::assertEquals('int_test456', $result->getPayload()->get('payment_intent_id'));
        self::assertEquals('req_refund_456', $result->getPayload()->get('request_id'));
        self::assertEquals('requested_by_customer', $result->getPayload()->get('reason'));
        self::assertEquals(['biz_order_id' => 'biz_123'], $result->getPayload()->get('metadata'));
    }
}
