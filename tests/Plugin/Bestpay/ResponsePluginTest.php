<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Bestpay\V1\ResponsePlugin;
use Yansongda\Supports\Collection;

it('成功', function () {
    $rocket = new Rocket();
    $rocket->setDestination(new Collection([
        'success' => true,
        'result' => ['tradeStatus' => 'SUCCESS'],
    ]));
    $rocket->setDestinationOrigin(new Response(200, [], '{}'));

    $result = (new ResponsePlugin())->assembly($rocket, fn ($r) => $r);

    expect($result->getDestination()->get('success'))->toBeTrue();
});

it('业务错误', function () {

    $rocket = new Rocket();
    $rocket->setDestination(new Collection([
        'success' => false,
        'errorCode' => 'API500',
        'errorMsg' => '系统繁忙',
    ]));
    $rocket->setDestinationOrigin(new Response(200, [], '{}'));

    (new ResponsePlugin())->assembly($rocket, fn ($r) => $r);
})->throws(InvalidResponseException::class);

it('HTTP 错误', function () {

    $rocket = new Rocket();
    $rocket->setDestination(new Collection(['success' => true]));
    $rocket->setDestinationOrigin(new Response(500, [], '{}'));

    (new ResponsePlugin())->assembly($rocket, fn ($r) => $r);
})->throws(InvalidResponseException::class);

