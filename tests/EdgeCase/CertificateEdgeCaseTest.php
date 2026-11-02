<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\V3\CallbackPlugin;
use Yansongda\Pay\Tests\Stubs\Plugin\CallbackPluginStub;

it('支付宝回调缺少签名', function () {
    $request = new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/alipay/notify',
        [],
        'trade_no=2023122122001499160501586202&trade_status=TRADE_SUCCESS'
    );

    Pay::alipay()->callback($request);
})->throws(InvalidSignException::class);

it('微信回调缺少签名头', function () {
    $request = new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/wechat/notify',
        [
            'Wechatpay-Nonce' => 'test_nonce',
            'Wechatpay-Timestamp' => '1626444144',
        ],
        json_encode([
            'resource' => [
                'algorithm' => 'AEAD_AES_256_GCM',
                'ciphertext' => 'test_ciphertext',
                'nonce' => 'test_nonce',
                'associated_data' => 'transaction',
                'original_type' => 'transaction',
            ],
            'event_type' => 'TRANSACTION.SUCCESS',
        ])
    );

    Pay::set(CallbackPlugin::class, new CallbackPluginStub());

    Pay::wechat()->callback($request);
})->throws(InvalidSignException::class);

it('Stripe webhook 缺少签名头', function () {
    $payload = json_encode([
        'id' => 'evt_test',
        'object' => 'event',
        'type' => 'payment_intent.succeeded',
    ]);

    $request = new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/stripe/webhook',
        [],
        $payload
    );

    Pay::stripe()->callback($request);
})->throws(InvalidSignException::class);

it('PayPal webhook 签名无效', function () {
    $payload = json_encode([
        'id' => 'WH-123',
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
    ]);

    $request = new ServerRequest(
        'POST',
        'https://pay.yansongda.cn/paypal/webhook',
        [
            'PAYPAL-TRANSMISSION-ID' => 'test_id',
            'PAYPAL-CERT-ID' => 'test_cert',
            'PAYPAL-TRANSMISSION-SIG' => 'invalid_signature',
            'PAYPAL-TRANSMISSION-TIME' => '2023-01-01T00:00:00Z',
        ],
        $payload
    );

    $response = new Response(200, [], json_encode(['verification_status' => 'FAILURE']));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($response);
    Pay::set(HttpClientInterface::class, $http);

    Pay::paypal()->callback($request);
})->throws(InvalidSignException::class);

afterEach(function () {
    Mockery::close();
Pay::clear();
});
