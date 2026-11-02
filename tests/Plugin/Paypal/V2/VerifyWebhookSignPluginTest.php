<?php

declare(strict_types=1);

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Paypal\V2\VerifyWebhookSignPlugin;

it('正常', function () {
    $plugin = new VerifyWebhookSignPlugin();
    $rocket = new Rocket();
    $rocket->setParams([
        '_verify_url' => 'https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature',
        '_verify_body' => '{"auth_algo":"SHA256withRSA"}',
        '_access_token' => 'test-token-123',
    ]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature');
    expect($result->getPayload()->get('_body'))->toEqual('{"auth_algo":"SHA256withRSA"}');
    expect($result->getPayload()->get('_access_token'))->toEqual('test-token-123');
});

