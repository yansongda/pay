<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayConfirmPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayConfirmPluginTest extends TestCase
{
    public function testSkipConfirmWhenNativeApiDisabled()
    {
        $rocket = (new Rocket())
            ->setPayload(new Collection([
                'amount' => 100,
                'currency' => 'USD',
            ]))
            ->setDestination(new Collection([
                'id' => 'int_test123',
                'client_secret' => 'cs_test123',
            ]));

        $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('int_test123', $result->getDestination()->get('id'));
        self::assertEquals('int_test123', $result->getDestination()->get('payment_intent_id'));
        self::assertEquals('cs_test123', $result->getDestination()->get('client_secret'));
        self::assertEquals('', $result->getDestination()->get('pay_url'));
    }

    public function testNormalizePayUrlFromRedirect()
    {
        $rocket = (new Rocket())
            ->setPayload(new Collection([
                '_native_api' => false,
            ]))
            ->setDestination(new Collection([
                'id' => 'int_test123',
                'next_action' => [
                    'type' => 'redirect',
                    'url' => 'https://pay.example.com/redirect',
                ],
            ]));

        $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('redirect', $result->getDestination()->get('next_action_type'));
        self::assertEquals('https://pay.example.com/redirect', $result->getDestination()->get('pay_url'));
    }
}
