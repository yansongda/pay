<?php

declare(strict_types=1);

use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Tests\Stubs\Traits\SupportServiceProviderPluginStub;

it('正常', function () {
    $config = Pay::get(ConfigInterface::class)->get('alipay.default');
    $config->setMode(PayMode::SERVICE);
    $config->setServiceProviderId('yansongda');

    $rocket = new Rocket();
    (new SupportServiceProviderPluginStub())->assembly($rocket);

    $result = json_encode($rocket->getParams());

    expect($result)->toContain('extend_params');
    expect($result)->toContain('sys_service_provider_id');
    expect($result)->toContain('yansongda');
});

