<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Apple\Pay\CallbackPlugin;
use Yansongda\Pay\Tests\Support\Apple\AppleJwsFactory;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Support\Traits\AppleCallbackHelpers;

uses(AppleCallbackHelpers::class);

it('回调成功', function () {
    $plugin = new CallbackPlugin();
    $signedTransactionInfo = AppleJwsFactory::makeJws([
        'transactionId' => '1000001234567890',
        'originalTransactionId' => '1000001234567890',
        'bundleId' => 'com.yansongda.pay.test',
        'productId' => 'com.yansongda.pay.vip',
        'purchaseDate' => 1691234567890,
        'originalPurchaseDate' => 1691234567890,
        'type' => 'Auto-Renewable Subscription',
        'inAppOwnershipType' => 'PURCHASED',
        'signedDate' => 1691234567890,
    ]);
    $payload = [
        'notificationType' => 'SUBSCRIBED',
        'subtype' => 'INITIAL_BUY',
        'notificationUUID' => '85b2f1e6-5c9a-4c3b-8f2e-9d1a4b6c7d8e',
        'signedDate' => 1691234567890,
        'version' => '2.0',
        'data' => [
            'appAppleId' => 123456789,
            'bundleId' => 'com.yansongda.pay.test',
            'environment' => 'Sandbox',
            'signedTransactionInfo' => $signedTransactionInfo,
        ],
    ];
    $jws = AppleJwsFactory::makeJws($payload);

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [
        'Content-Type' => 'application/json',
    ], json_encode(['signedPayload' => $jws]));

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getPayload())->toBeInstanceOf(Collection::class);
    expect($result->getPayload()->all())->not->toBeEmpty();
    expect($result->getPayload()->get('notificationType'))->toEqual('SUBSCRIBED');
    expect($result->getPayload()->get('subtype'))->toEqual('INITIAL_BUY');
    expect($result->getPayload()->get('notificationUUID'))->toEqual('85b2f1e6-5c9a-4c3b-8f2e-9d1a4b6c7d8e');
    expect($result->getPayload()->get('data.signedTransactionInfo'))->toBeArray();
    expect($result->getPayload()->get('data.signedTransactionInfo.transactionId'))->toEqual('1000001234567890');
});

it('非法 body 抛出异常', function () {
    $plugin = new CallbackPlugin();
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], 'not-valid-json');

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_APPLE_TOKEN_INVALID);

it('缺少待签名 payload 抛出异常', function () {
    $plugin = new CallbackPlugin();
    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], json_encode(['foo' => 'bar']));

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidSignException::class, null, Exception::SIGN_EMPTY);

it('payload 被篡改抛出异常', function () {
    $plugin = new CallbackPlugin();
    $jws = AppleJwsFactory::makeJws(['notificationType' => 'SUBSCRIBED']);
    $tampered = $this->tamperJwsPayload($jws, ['notificationType' => 'EVIL']);

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], json_encode(['signedPayload' => $tampered]));

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('嵌套 JWS 被篡改抛出异常', function () {
    $plugin = new CallbackPlugin();
    $inner = AppleJwsFactory::makeJws(['transactionId' => '1000001234567890']);
    $payload = [
        'notificationType' => 'SUBSCRIBED',
        'data' => [
            'bundleId' => 'com.yansongda.pay.test',
            'environment' => 'Sandbox',
            'signedTransactionInfo' => $inner,
        ],
    ];
    $payload['data']['signedTransactionInfo'] = $this->tamperJwsPayload($inner, ['transactionId' => 'EVIL']);
    $jws = AppleJwsFactory::makeJws($payload);

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], json_encode(['signedPayload' => $jws]));

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('内层 JWS 缺少 x5c 被拒绝', function () {
    $plugin = new CallbackPlugin();
    // 对齐官方库：二级 JWS 须自带 x5c，无 x5c 一律拒绝
    $inner = $this->makeInnerJws(['transactionId' => '1000001234567890']);
    $jws = AppleJwsFactory::makeJws([
        'notificationType' => 'SUBSCRIBED',
        'data' => [
            'bundleId' => 'com.yansongda.pay.test',
            'environment' => 'Sandbox',
            'signedTransactionInfo' => $inner,
        ],
    ]);

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], json_encode(['signedPayload' => $jws]));

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $request, '_params' => []]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('非法请求抛出异常', function () {
    $plugin = new CallbackPlugin();
    $rocket = new Rocket();
    $rocket->setParams(['_request' => ['foo' => 'bar'], '_params' => []]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

it('Provider 回调链接', function () {
    $jws = AppleJwsFactory::makeJws([
        'notificationType' => 'DID_RENEW',
        'subtype' => 'RESUBSCRIBE',
        'notificationUUID' => '85b2f1e6-5c9a-4c3b-8f2e-9d1a4b6c7d8e',
        'signedDate' => 1691234567890,
        'version' => '2.0',
        'data' => [
            'bundleId' => 'com.yansongda.pay.test',
            'environment' => 'Sandbox',
            'signedTransactionInfo' => AppleJwsFactory::makeJws([
                'transactionId' => '1000001234567890',
                'originalTransactionId' => '1000001234567890',
                'bundleId' => 'com.yansongda.pay.test',
                'productId' => 'com.yansongda.pay.vip',
                'purchaseDate' => 1691234567890,
                'originalPurchaseDate' => 1691234567890,
                'type' => 'Auto-Renewable Subscription',
                'inAppOwnershipType' => 'PURCHASED',
                'signedDate' => 1691234567890,
            ]),
        ],
    ]);

    $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [
        'Content-Type' => 'application/json',
    ], json_encode(['signedPayload' => $jws]));

    $result = Pay::apple()->callback($request);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->get('notificationType'))->toEqual('DID_RENEW');
    expect($result->get('subtype'))->toEqual('RESUBSCRIBE');
    expect($result->get('notificationUUID'))->toEqual('85b2f1e6-5c9a-4c3b-8f2e-9d1a4b6c7d8e');
    expect($result->get('data.signedTransactionInfo'))->toBeArray();
    expect($result->get('data.signedTransactionInfo.transactionId'))->toEqual('1000001234567890');
});
