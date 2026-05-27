<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Wechat\Virtual\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CallbackPluginTest extends TestCase
{
    protected CallbackPlugin $plugin;

    /**
     * 43 字符的 EncodingAESKey，对应 32 字节 AES 密钥: base64_decode(key.'=') = '0123456789abcdef0123456789abcdef'.
     */
    protected string $encodingAesKey = 'MDEyMzQ1Njc4OWFiY2RlZjAxMjM0NTY3ODlhYmNkZWY';

    protected string $callbackToken = 'test_callback_token';

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testXmlCallbackGoodsDeliverNotify()
    {
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

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Collection::class, $result->getDestination());
        self::assertEquals('event', $result->getDestination()->get('MsgType'));
        self::assertEquals('xpay_goods_deliver_notify', $result->getDestination()->get('Event'));
        self::assertEquals(NoHttpRequestDirection::class, $result->getDirection());
    }

    public function testXmlCallbackCoinPayNotify()
    {
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

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('xpay_coin_pay_notify', $result->getDestination()->get('Event'));
    }

    public function testXmlCallbackRefundNotify()
    {
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

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('xpay_refund_notify', $result->getDestination()->get('Event'));
    }

    public function testJsonCallback()
    {
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

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Collection::class, $result->getDestination());
        self::assertEquals('xpay_goods_deliver_notify', $result->getDestination()->get('Event'));
    }

    public function testInvalidRequest()
    {
        $rocket = (new Rocket())->setParams([]);

        $this->expectException(\Yansongda\Artful\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage('参数异常: 微信虚拟支付回调参数不正确');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testInvalidSignature()
    {
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

        $this->expectException(InvalidSignException::class);
        $this->expectExceptionMessage('签名异常: 验证微信虚拟支付回调签名失败');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testEmptySignature()
    {
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

        $this->expectException(InvalidSignException::class);
        $this->expectExceptionMessage('签名异常: 微信虚拟支付回调签名为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    private function encryptMessage(string $message, string $encodingAesKey, string $appId): string
    {
        $key = base64_decode($encodingAesKey.'=');
        $iv = substr($key, 0, 16);

        $random = str_repeat("\x00", 16);
        $content = $random.pack('N', strlen($message)).$message.$appId;

        // PKCS7 padding with 32-byte block size
        $blockSize = 32;
        $pad = $blockSize - (strlen($content) % $blockSize);
        $content .= str_repeat(chr($pad), $pad);

        $encrypted = openssl_encrypt($content, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        return base64_encode($encrypted);
    }

    private function createSignature(string $token, string $timestamp, string $nonce, string $encrypt): string
    {
        $arr = [$token, $timestamp, $nonce, $encrypt];
        sort($arr, SORT_STRING);

        return sha1(implode('', $arr));
    }
}
