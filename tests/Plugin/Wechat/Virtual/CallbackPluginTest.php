<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Wechat\Virtual\CallbackPlugin;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Tests\Support\Traits\WechatVirtualCallbackHelpers;

uses(WechatVirtualCallbackHelpers::class);

it('XML 发货回调', function () {
    $plugin = new CallbackPlugin();
    $appId = 'wx55955316af4ef14';
    $message = '<xml><MsgType>event</MsgType><Event>xpay_goods_deliver_notify</Event></xml>';
    $encrypt = $this->encryptMessage($message, $this->encodingAesKey, $appId);

    $timestamp = '1626444144';
    $nonce = 'test_nonce';
    $signature = $this->createSignature($this->callbackToken, $timestamp, $nonce, $encrypt);

    $body = '<xml><ToUserName><![CDATA[gh_1234567]]></ToUserName><Encrypt><![CDATA['.$encrypt.']]></Encrypt></xml>';

    $request = (new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/wechat/virtual/callback',
        ['Content-Type' => 'application/xml'],
        $body
    ))->withQueryParams([
        'msg_signature' => $signature,
        'timestamp' => $timestamp,
        'nonce' => $nonce,
    ]);

    $rocket = (new Rocket())->setParams(['_request' => $request]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination())->toBeInstanceOf(Collection::class);
    expect($result->getDestination()->get('MsgType'))->toEqual('event');
    expect($result->getDestination()->get('Event'))->toEqual('xpay_goods_deliver_notify');
    expect($result->getDirection())->toEqual(NoHttpRequestDirection::class);
});

it('XML 金币支付回调', function () {
    $plugin = new CallbackPlugin();
    $appId = 'wx55955316af4ef14';
    $message = '<xml><MsgType>event</MsgType><Event>xpay_coin_pay_notify</Event></xml>';
    $encrypt = $this->encryptMessage($message, $this->encodingAesKey, $appId);

    $timestamp = '1626444144';
    $nonce = 'test_nonce';
    $signature = $this->createSignature($this->callbackToken, $timestamp, $nonce, $encrypt);

    $body = '<xml><ToUserName><![CDATA[gh_1234567]]></ToUserName><Encrypt><![CDATA['.$encrypt.']]></Encrypt></xml>';

    $request = (new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/wechat/virtual/callback',
        ['Content-Type' => 'application/xml'],
        $body
    ))->withQueryParams([
        'msg_signature' => $signature,
        'timestamp' => $timestamp,
        'nonce' => $nonce,
    ]);

    $rocket = (new Rocket())->setParams(['_request' => $request]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination()->get('Event'))->toEqual('xpay_coin_pay_notify');
});

it('XML 退款回调', function () {
    $plugin = new CallbackPlugin();
    $appId = 'wx55955316af4ef14';
    $message = '<xml><MsgType>event</MsgType><Event>xpay_refund_notify</Event></xml>';
    $encrypt = $this->encryptMessage($message, $this->encodingAesKey, $appId);

    $timestamp = '1626444144';
    $nonce = 'test_nonce';
    $signature = $this->createSignature($this->callbackToken, $timestamp, $nonce, $encrypt);

    $body = '<xml><ToUserName><![CDATA[gh_1234567]]></ToUserName><Encrypt><![CDATA['.$encrypt.']]></Encrypt></xml>';

    $request = (new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/wechat/virtual/callback',
        ['Content-Type' => 'application/xml'],
        $body
    ))->withQueryParams([
        'msg_signature' => $signature,
        'timestamp' => $timestamp,
        'nonce' => $nonce,
    ]);

    $rocket = (new Rocket())->setParams(['_request' => $request]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination()->get('Event'))->toEqual('xpay_refund_notify');
});

it('JSON 回调', function () {
    $plugin = new CallbackPlugin();
    $appId = 'wx55955316af4ef14';
    $message = '<xml><MsgType>event</MsgType><Event>xpay_goods_deliver_notify</Event></xml>';
    $encrypt = $this->encryptMessage($message, $this->encodingAesKey, $appId);

    $timestamp = '1626444144';
    $nonce = 'test_nonce';
    $signature = $this->createSignature($this->callbackToken, $timestamp, $nonce, $encrypt);

    $body = json_encode(['Encrypt' => $encrypt]);

    $request = (new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/wechat/virtual/callback',
        ['Content-Type' => 'application/json'],
        $body
    ))->withQueryParams([
        'msg_signature' => $signature,
        'timestamp' => $timestamp,
        'nonce' => $nonce,
    ]);

    $rocket = (new Rocket())->setParams(['_request' => $request]);

    $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

    expect($result->getDestination())->toBeInstanceOf(Collection::class);
    expect($result->getDestination()->get('Event'))->toEqual('xpay_goods_deliver_notify');
});

it('请求非法', function () {
    $plugin = new CallbackPlugin();
    $rocket = (new Rocket())->setParams([]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(\Yansongda\Artful\Exception\InvalidParamsException::class, '参数异常: 微信虚拟支付回调参数不正确');

it('签名无效', function () {
    $plugin = new CallbackPlugin();
    $appId = 'wx55955316af4ef14';
    $message = '<xml><MsgType>event</MsgType><Event>xpay_goods_deliver_notify</Event></xml>';
    $encrypt = $this->encryptMessage($message, $this->encodingAesKey, $appId);

    $body = '<xml><ToUserName><![CDATA[gh_1234567]]></ToUserName><Encrypt><![CDATA['.$encrypt.']]></Encrypt></xml>';

    $request = (new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/wechat/virtual/callback',
        [],
        $body
    ))->withQueryParams([
        'msg_signature' => 'invalid_signature',
        'timestamp' => '1626444144',
        'nonce' => 'test_nonce',
    ]);

    $rocket = (new Rocket())->setParams(['_request' => $request]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidSignException::class, '签名异常: 验证微信虚拟支付回调签名失败');

it('空签名', function () {
    $plugin = new CallbackPlugin();
    $appId = 'wx55955316af4ef14';
    $message = '<xml><MsgType>event</MsgType><Event>xpay_goods_deliver_notify</Event></xml>';
    $encrypt = $this->encryptMessage($message, $this->encodingAesKey, $appId);

    $body = '<xml><ToUserName><![CDATA[gh_1234567]]></ToUserName><Encrypt><![CDATA['.$encrypt.']]></Encrypt></xml>';

    $request = (new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/wechat/virtual/callback',
        [],
        $body
    ))->withQueryParams([
        'timestamp' => '1626444144',
        'nonce' => 'test_nonce',
    ]);

    $rocket = (new Rocket())->setParams(['_request' => $request]);

    $plugin->assembly($rocket, function ($rocket) { return $rocket; });
})->throws(InvalidSignException::class, '签名异常: 微信虚拟支付回调签名为空');
