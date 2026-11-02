<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Apple;

it('pay', function () {
    $apple = Pay::apple();

    expect($apple)->toBeInstanceOf(Apple::class);
    expect(Pay::get('apple'))->toBeInstanceOf(Apple::class);
    expect(Pay::get(Apple::class))->toBeInstanceOf(Apple::class);
});

it('cancel', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_METHOD_NOT_SUPPORTED);

    Pay::apple()->cancel([]);
});

it('close', function () {
    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_METHOD_NOT_SUPPORTED);

    Pay::apple()->close([]);
});

it('success', function () {
    $result = Pay::apple()->success();

    expect($result)->toBeInstanceOf(ResponseInterface::class);
    expect($result->getStatusCode())->toEqual(200);
    expect((string) $result->getBody())->toEqual('{"result":"success"}');
    expect($result->getHeaderLine('Content-Type'))->toContain('application/json');
});

it('url', function () {
    expect(Apple::URL[PayMode::NORMAL->value])->toEqual('https://api.storekit.apple.com');
    expect(Apple::URL[PayMode::SANDBOX->value])->toEqual('https://api.storekit-sandbox.apple.com');
    expect(Apple::URL[PayMode::SERVICE->value])->toEqual('https://api.storekit.apple.com');
});

