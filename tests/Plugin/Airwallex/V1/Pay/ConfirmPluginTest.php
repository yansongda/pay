<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\ConfirmPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class ConfirmPluginTest extends TestCase
{
    public function testMissingId()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        (new ConfirmPlugin())->assembly((new Rocket())->setPayload(new Collection([])), fn ($rocket) => $rocket);
    }

    public function testNormal()
    {
        $result = (new ConfirmPlugin())->assembly(
            (new Rocket())->setPayload(new Collection([
                'payment_intent_id' => 'int_test123',
                'payment_method' => ['type' => 'card'],
            ])),
            fn ($rocket) => $rocket
        );

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('api/v1/pa/payment_intents/int_test123/confirm', $result->getPayload()->get('_url'));
        self::assertNotEmpty($result->getPayload()->get('request_id'));
    }
}
