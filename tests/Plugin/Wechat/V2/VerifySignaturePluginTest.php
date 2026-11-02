<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V2\VerifySignaturePlugin;
use Yansongda\Supports\Collection;

it('不应发起请求', function () {
    $plugin = new VerifySignaturePlugin();
    $rocket = new Rocket();
    $rocket->setDirection(NoHttpRequestDirection::class)->setDestinationOrigin(new Response());
    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });
    expect($result)->toBe($rocket);
});

it('正常', function () {
    $plugin = new VerifySignaturePlugin();
    $rocket = new Rocket();
    $rocket->setDestination(new Collection(['name' => 'yansongda', 'age' => 29, 'foo' => '', 'sign' => '3213848AED2C380749FD1D559555881D']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect(true)->toBeTrue();
});

