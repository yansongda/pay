<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\ResponsePlugin;
use Yansongda\Supports\Collection;

it('成功响应', function () {
    $plugin = new ResponsePlugin();
    $destination = [
        'err_no' => 0,
        'err_msg' => 'success',
        'log_id' => '20240101123456000000000000',
        'data' => ['order_id' => '123456'],
    ];

    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(200));
    $rocket->setDestination(new Collection($destination));

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination())->toBeInstanceOf(Collection::class);
    expect($result->getDestination()->all())->toEqual($destination);
});

it('业务错误响应抛出异常', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(200));
    $rocket->setDestination(new Collection([
        'err_no' => 10000,
        'err_msg' => '参数错误',
        'log_id' => '20240101123456000000000000',
    ]));

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_BUSINESS_CODE_WRONG);
    $this->expectExceptionMessage('抖音返回业务异常: err_no=10000, err_msg=参数错误');

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

it('服务端错误抛出异常', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(500));

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
});

