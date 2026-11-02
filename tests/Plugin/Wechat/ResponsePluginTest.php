<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Supports\Collection;

it('原始响应 destination', function () {
    $plugin = new ResponsePlugin();
    $destination = new Response();

    $rocket = new Rocket();
    $rocket->setDestinationOrigin($destination);
    $rocket->setDestination(new Collection());

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination())->toBeInstanceOf(Collection::class);
});

it('原始响应码错误 destination', function () {
    $plugin = new ResponsePlugin();
    $destination = new Response(500);

    $rocket = new Rocket();
    $rocket->setDestinationOrigin($destination);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidResponseException::class, null, Exception::RESPONSE_CODE_WRONG);

