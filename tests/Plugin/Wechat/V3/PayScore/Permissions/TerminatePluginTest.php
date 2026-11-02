<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Direction\OriginResponseDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\TerminatePlugin;
use Yansongda\Supports\Collection;

it('empty payload', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 解除支付分预授权（签约），参数为空');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('service id missing', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'openid' => 'oXopenid123',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
    $this->expectExceptionMessage('参数异常: 缺少支付分服务ID -- [service_id]');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('missing openid and authorization code', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'reason' => 'test',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 解除支付分预授权（签约），参数缺少 `openid` 或 `authorization_code`');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('openid and authorization code both filled', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'oXopenid123',
        'authorization_code' => 'AUTH_CODE',
        'reason' => 'test',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
    $this->expectExceptionMessage('参数异常: 解除支付分预授权（签约），`openid` 与 `authorization_code` 不允许同时填写');

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('service mode rejected', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'openid' => 'oXopenid123',
        'reason' => 'test',
    ]));

    $this->expectException(InvalidParamsException::class);
    $this->expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
});

it('openid branch', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'oXopenid123',
        'appid' => 'wx_payload_override',
        'reason' => 'test',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->toEqual(OriginResponseDirection::class);
    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/payscore/permissions/openid/oXopenid123/terminate');
    expect($result->getPayload()->has('openid'))->toBeFalse();
    expect($result->getPayload()->get('appid'))->toEqual('wx_payload_override');
    expect($result->getPayload()->get('service_id'))->toEqual('12345678901234567890123456789012');
    expect($result->getPayload()->get('reason'))->toEqual('test');
});

it('openid branch app id from config', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'oXopenid123',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload()->get('_url'))->toEqual('/v3/payscore/permissions/openid/oXopenid123/terminate');
    expect($result->getPayload()->get('appid'))->toEqual('wx55955316af4ef13');
    expect($result->getPayload()->get('service_id'))->toEqual('12345678901234567890123456789012');
    expect($result->getPayload()->has('openid'))->toBeFalse();
});

it('authorization code branch', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'authorization_code' => 'AUTH_CODE',
        'reason' => 'test',
    ]));

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDirection())->toEqual(OriginResponseDirection::class);
    expect($result->getPayload()->get('_method'))->toEqual('POST');
    expect($result->getPayload()->get('_url'))->toEqual('/v3/payscore/permissions/authorization-code/AUTH_CODE/terminate');
    expect($result->getPayload()->has('authorization_code'))->toBeFalse();
    expect($result->getPayload()->has('appid'))->toBeFalse();
    expect($result->getPayload()->get('service_id'))->toEqual('12345678901234567890123456789012');
    expect($result->getPayload()->get('reason'))->toEqual('test');
});

it('response204 with origin response direction', function () {
    $response = new Response(204);
    $rocket = new Rocket();
    $rocket->setDirection(OriginResponseDirection::class)
        ->setDestinationOrigin($response)
        ->setDestination($response);

    $result = (new ResponsePlugin())->assembly($rocket, fn ($r) => (new ParserPlugin())->assembly($r, fn ($r2) => $r2));

    $destination = $result->getDestination();
    expect($destination)->toBeInstanceOf(ResponseInterface::class);
    expect($destination->getStatusCode())->toEqual(204);
    expect((string) $destination->getBody())->toBe('');
});

