<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Airwallex\V1\ResponsePlugin;
use Yansongda\Supports\Collection;

it('成功响应', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(200));
    $rocket->setDestination(new Collection(['id' => 'int_test_123']));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination()->get('id'))->toEqual('int_test_123');
});

it('创建成功响应', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(201));
    $rocket->setDestination(new Collection(['id' => 'int_test_456']));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination()->get('id'))->toEqual('int_test_456');
});

it('客户端错误抛出异常', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(400));
    $rocket->setDestination(new Collection(['code' => 'validation_error']));

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidResponseException::class, null, Exception::RESPONSE_CODE_WRONG);

it('服务端错误抛出异常', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(500));

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidResponseException::class, null, Exception::RESPONSE_CODE_WRONG);

