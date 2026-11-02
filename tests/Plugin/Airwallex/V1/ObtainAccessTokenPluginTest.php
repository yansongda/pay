<?php

declare(strict_types=1);

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AirwallexConfig;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Airwallex\V1\ObtainAccessTokenPlugin;

it('正常', function () {
    /** @var AirwallexConfig $config */
    $config = Pay::get(ConfigInterface::class)->get('airwallex.default');
    $config->setAccessToken('cached_airwallex_token');
    $config->setAccessTokenExpiry(time() + 3600);

    $rocket = (new Rocket())->setParams([]);

    $result = (new ObtainAccessTokenPlugin())->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_access_token'))->toEqual('cached_airwallex_token');
});

