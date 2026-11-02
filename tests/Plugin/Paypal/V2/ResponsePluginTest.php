<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Paypal\V2\ResponsePlugin;
use Yansongda\Supports\Collection;

it('成功响应', function () {
    $plugin = new ResponsePlugin();
    $destination = ['id' => 'ORDER_123', 'status' => 'CREATED'];

    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(201));
    $rocket->setDestination(new Collection($destination));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination())->toBeInstanceOf(Collection::class);
    expect($result->getDestination()->all())->toEqual($destination);
});

it('非成功响应抛出异常', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(400));
    $rocket->setDestination(new Collection(['error' => 'INVALID_REQUEST']));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidResponseException::class, null, Exception::RESPONSE_CODE_WRONG);

it('服务端错误抛出异常', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(500));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidResponseException::class, null, Exception::RESPONSE_CODE_WRONG);

