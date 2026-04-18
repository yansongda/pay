<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\GetAccessTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class GetAccessTokenPluginTest extends TestCase
{
    public function testNormal()
    {
        $result = (new GetAccessTokenPlugin())->assembly(new Rocket(), fn ($rocket) => $rocket);

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('api/v1/authentication/login', $result->getPayload()->get('_url'));
        self::assertEquals('client', $result->getPayload()->get('_auth_type'));
    }
}
