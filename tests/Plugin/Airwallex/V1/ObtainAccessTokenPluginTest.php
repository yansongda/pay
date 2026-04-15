<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

class ObtainAccessTokenPluginTest extends TestCase
{
    public function testNormal()
    {
        Pay::get(ConfigInterface::class)->set('airwallex.default._access_token', 'cached_airwallex_token');
        Pay::get(ConfigInterface::class)->set('airwallex.default._access_token_expiry', time() + 3600);

        $rocket = (new Rocket())->setParams([]);

        $result = (new ObtainAccessTokenPlugin())->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('cached_airwallex_token', $result->getPayload()->get('_access_token'));
    }
}
