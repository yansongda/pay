<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V2\ObtainAccessTokenPlugin;

it('外部 access token', function () {
    $plugin = new ObtainAccessTokenPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_access_token' => 'external_token_123']);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_access_token'))->toEqual('external_token_123');
});

