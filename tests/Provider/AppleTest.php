<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Apple;

it('支付', function () {
    $apple = Pay::apple();

    expect($apple)->toBeInstanceOf(Apple::class);
    expect(Pay::get('apple'))->toBeInstanceOf(Apple::class);
    expect(Pay::get(Apple::class))->toBeInstanceOf(Apple::class);
});

it('撤销', function () {

    Pay::apple()->cancel([]);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_METHOD_NOT_SUPPORTED);

it('关单', function () {

    Pay::apple()->close([]);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_METHOD_NOT_SUPPORTED);

it('成功', function () {
    $result = Pay::apple()->success();

    expect($result)->toBeInstanceOf(ResponseInterface::class);
    expect($result->getStatusCode())->toEqual(200);
    expect((string) $result->getBody())->toEqual('{"result":"success"}');
    expect($result->getHeaderLine('Content-Type'))->toContain('application/json');
});

it('URL', function () {
    expect(Apple::URL[PayMode::NORMAL->value])->toEqual('https://api.storekit.apple.com');
    expect(Apple::URL[PayMode::SANDBOX->value])->toEqual('https://api.storekit-sandbox.apple.com');
    expect(Apple::URL[PayMode::SERVICE->value])->toEqual('https://api.storekit.apple.com');
});

