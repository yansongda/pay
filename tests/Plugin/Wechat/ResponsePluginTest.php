<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Supports\Collection;

it('original response destination', function () {
    $plugin = new ResponsePlugin();
    $destination = new Response();

    $rocket = new Rocket();
    $rocket->setDestinationOrigin($destination);
    $rocket->setDestination(new Collection());

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination())->toBeInstanceOf(Collection::class);
});

it('original response code error destination', function () {
    $plugin = new ResponsePlugin();
    $destination = new Response(500);

    $rocket = new Rocket();
    $rocket->setDestinationOrigin($destination);

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

