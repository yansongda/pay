<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1;

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AirwallexConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;
use Yansongda\Pay\Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ObtainAccessTokenPluginTest extends TestCase
{
    public function testNormal()
    {
        /** @var AirwallexConfig $config */
        $config = Pay::get(ConfigInterface::class)->get('airwallex.default');
        $config->setAccessToken('cached_airwallex_token');
        $config->setAccessTokenExpiry(time() + 3600);

        $rocket = (new Rocket())->setParams([]);

        $result = (new ObtainAccessTokenPlugin())->assembly($rocket, fn ($rocket) => $rocket);

        self::assertEquals('cached_airwallex_token', $result->getPayload()->get('_access_token'));
    }
}
