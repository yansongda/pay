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

it('空 payload', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 解除支付分预授权（签约），参数为空', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('缺少服务商 ID', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
        'openid' => 'oXopenid123',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 缺少支付分服务ID -- [service_id]', Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);

it('缺少 openid 与授权码', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'reason' => 'test',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 解除支付分预授权（签约），参数缺少 `openid` 或 `authorization_code`', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('openid 与授权码同时填写', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setPayload(new Collection([
        'openid' => 'oXopenid123',
        'authorization_code' => 'AUTH_CODE',
        'reason' => 'test',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, '参数异常: 解除支付分预授权（签约），`openid` 与 `authorization_code` 不允许同时填写', Exception::PARAMS_NECESSARY_PARAMS_MISSING);

it('服务商模式被拒绝', function () {
    $plugin = new TerminatePlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
        'openid' => 'oXopenid123',
        'reason' => 'test',
    ]));

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);

it('openid 分支', function () {
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

it('openid 分支使用配置中的 app_id', function () {
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

it('授权码分支', function () {
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

it('204 响应沿原始响应方向', function () {
    // 204 无包体回归断言：TerminatePlugin 两分支均设置 OriginResponseDirection，
    // 响应链（ResponsePlugin -> ParserPlugin）遇到 204 空 body 不应抛异常，destination 原样为 ResponseInterface.
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

