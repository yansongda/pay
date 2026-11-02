<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\ResponsePlugin;
use Yansongda\Supports\Collection;

it('success response', function () {
    $plugin = new ResponsePlugin();
    $destination = ['id' => 'pi_test123', 'status' => 'requires_payment_method'];

    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(200));
    $rocket->setDestination(new Collection($destination));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination())->toBeInstanceOf(Collection::class);
    expect($result->getDestination()->all())->toEqual($destination);
});

it('created response', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(201));
    $rocket->setDestination(new Collection(['id' => 'pi_test456']));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination())->toBeInstanceOf(Collection::class);
});

it('client error throws exception', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(400));
    $rocket->setDestination(new Collection(['error' => ['type' => 'invalid_request_error']]));

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('server error throws exception', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(500));

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('unauthorized throws exception', function () {
    $plugin = new ResponsePlugin();
    $rocket = new Rocket();
    $rocket->setDestinationOrigin(new Response(401));
    $rocket->setDestination(new Collection(['error' => ['type' => 'authentication_error']]));

    $this->expectException(InvalidResponseException::class);
    $this->expectExceptionCode(Exception::RESPONSE_CODE_WRONG);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

