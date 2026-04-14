<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\RefundPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundPluginTest extends TestCase
{
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
}
