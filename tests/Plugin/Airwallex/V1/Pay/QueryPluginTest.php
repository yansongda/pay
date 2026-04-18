<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\QueryPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryPluginTest extends TestCase
{
    public function testMissingId()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        (new QueryPlugin())->assembly((new Rocket())->setPayload(new Collection([])), fn ($rocket) => $rocket);
    }

    public function testNormal()
    {
        $result = (new QueryPlugin())->assembly(
            (new Rocket())->setPayload(new Collection(['id' => 'int_test123'])),
            fn ($rocket) => $rocket
        );

        self::assertEquals('GET', $result->getPayload()->get('_method'));
        self::assertEquals('api/v1/pa/payment_intents/int_test123', $result->getPayload()->get('_url'));
    }
}
