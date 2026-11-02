<?php

declare(strict_types=1);

use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Douyin\V1\CallbackPlugin;
use Yansongda\Pay\Tests\Support\Traits\DouyinHelpers;

uses(DouyinHelpers::class);

it('支付回调', function () {
    $plugin = new CallbackPlugin();
    $msg = '{"order_id":"7398108028895054107","out_trade_no":"yansongda","total_amount":1,"status":"SUCCESS","seller_uid":"73744242495132490630","paid_at":1722769986}';
    $body = '{"version":"3.0","type":"payment","msg":'.json_encode($msg).'}';

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $this->getDouyinCallbackRequest($body)]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->all())->toBe(json_decode($msg, true));
    expect($result->getDestination()->all())->toBe(json_decode($msg, true));
    expect($result->getDirection())->toBe(NoHttpRequestDirection::class);
});

it('退款回调', function () {
    $plugin = new CallbackPlugin();
    $msg = [
        'refund_id' => '7398108028895054107',
        'out_refund_no' => '202408041111312119',
        'out_trade_no' => 'yansongda-trade-001',
        'status' => 'SUCCESS',
        'refund_amount' => 1,
    ];
    $body = json_encode(['version' => '3.0', 'type' => 'refund', 'msg' => json_encode($msg)]);

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $this->getDouyinCallbackRequest($body)]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->all())->toBe($msg);
    expect($result->getDestination()->all())->toBe($msg);
    expect($result->getDirection())->toBe(NoHttpRequestDirection::class);
});

it('预创建退款回调', function () {
    $plugin = new CallbackPlugin();
    $msg = [
        'refund_id' => '7398108028895054107',
        'out_refund_no' => '202408041111312119',
        'out_trade_no' => 'yansongda-trade-001',
        'need_refund_audit' => true,
        'refund_amount' => 1,
    ];
    $body = json_encode(['version' => '3.0', 'type' => 'pre_create_refund', 'msg' => json_encode($msg)]);

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $this->getDouyinCallbackRequest($body)]);

    $result = $plugin->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getPayload()->all())->toBe($msg);
    expect($result->getDestination()->all())->toBe($msg);
    expect($result->getDestination()->get('need_refund_audit'))->toBeTrue();
    expect($result->getDirection())->toBe(NoHttpRequestDirection::class);
});

it('签名被篡改', function () {
    $plugin = new CallbackPlugin();
    $msg = '{"order_id":"7398108028895054107","out_trade_no":"yansongda","total_amount":1,"status":"SUCCESS"}';
    $body = '{"version":"3.0","type":"payment","msg":'.json_encode($msg).'}';

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $this->getDouyinCallbackRequest($body, ['Byte-Signature' => base64_encode('tampered-signature')])]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidSignException::class, null, Exception::SIGN_ERROR);

it('缺少 type', function () {
    $plugin = new CallbackPlugin();
    $msg = ['order_id' => '7398108028895054107'];
    $body = json_encode(['version' => '3.0', 'msg' => json_encode($msg)]);

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $this->getDouyinCallbackRequest($body)]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

it('空类型', function () {
    $plugin = new CallbackPlugin();
    $msg = ['order_id' => '7398108028895054107'];
    $body = json_encode(['version' => '3.0', 'type' => '', 'msg' => json_encode($msg)]);

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $this->getDouyinCallbackRequest($body)]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

it('非字符串类型', function () {
    $plugin = new CallbackPlugin();
    $body = json_encode(['version' => '3.0', 'type' => 123, 'msg' => '{}']);

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $this->getDouyinCallbackRequest($body)]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

it('msg 非法', function () {
    $plugin = new CallbackPlugin();
    $body = '{"version":"3.0","type":"payment","msg":"not-a-json"}';

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $this->getDouyinCallbackRequest($body)]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

it('body 非法', function () {
    $plugin = new CallbackPlugin();
    $body = 'not-a-json';

    $rocket = new Rocket();
    $rocket->setParams(['_request' => $this->getDouyinCallbackRequest($body)]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);

it('缺少请求对象', function () {
    $plugin = new CallbackPlugin();
    $rocket = new Rocket();
    $rocket->setParams([]);

    $plugin->assembly($rocket, fn ($rocket) => $rocket);
})->throws(InvalidParamsException::class, null, Exception::PARAMS_CALLBACK_REQUEST_INVALID);
