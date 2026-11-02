<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Airwallex\V1\GetAccessTokenPlugin;

it('正常', function () {
    $result = (new GetAccessTokenPlugin())->assembly(new Rocket(), fn ($rocket) => $rocket);

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/api/v1/authentication/login');
    expect($result->getPayload()->get('_auth_type'))->toEqual('client');
});

