<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Apple\Pay\PayTokenPlugin;
use Yansongda\Pay\Tests\Support\Apple\AppleTokenFactory;

it('正常', function () {
    $plugin = new PayTokenPlugin();
    $token = AppleTokenFactory::makeToken(['applicationPrimaryAccountID' => '123456', 'totalAmount' => '100']);

    $rocket = new Rocket();
    $rocket->setParams(['token' => $token]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->toEqual(NoHttpRequestDirection::class);
    expect($result->getPayload()->get('applicationPrimaryAccountID'))->toEqual('123456');
    expect($result->getPayload()->get('totalAmount'))->toEqual('100');
    expect($result->getPayload()->get('_token_header'))->toEqual($token['header']);
    expect($result->getPayload())->toBe($result->getDestination());
});

it('下划线 token', function () {
    $plugin = new PayTokenPlugin();
    $token = AppleTokenFactory::makeToken(['applicationPrimaryAccountID' => '888888']);

    $rocket = new Rocket();
    $rocket->setParams(['_token' => $token]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('applicationPrimaryAccountID'))->toEqual('888888');
    expect($result->getPayload()->get('_token_header'))->toEqual($token['header']);
});

it('缺少 token 抛出异常', function () {
    $plugin = new PayTokenPlugin();

    $rocket = new Rocket();
    $rocket->setParams([]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_APPLE_TOKEN_INVALID);

it('非法 token 抛出异常', function () {
    $plugin = new PayTokenPlugin();

    $rocket = new Rocket();
    $rocket->setParams(['token' => ['data' => 'invalid']]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_APPLE_TOKEN_INVALID);

