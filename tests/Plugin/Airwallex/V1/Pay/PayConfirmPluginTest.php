<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Rocket;
use Yansongda\Artful\Contract\HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayConfirmPlugin;
use Yansongda\Supports\Collection;

it('未启用 native api 时跳过确认', function () {
    $rocket = (new Rocket())
        ->setPayload(new Collection([
            'amount' => 100,
            'currency' => 'USD',
        ]))
        ->setDestination(new Collection([
            'id' => 'int_test123',
            'client_secret' => 'cs_test123',
        ]));

    $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination()->get('id'))->toEqual('int_test123');
    expect($result->getDestination()->get('payment_intent_id'))->toEqual('int_test123');
    expect($result->getDestination()->get('client_secret'))->toEqual('cs_test123');
    expect($result->getDestination()->get('pay_url'))->toEqual('');
});

it('重定向归一化支付 URL', function () {
    $rocket = (new Rocket())
        ->setPayload(new Collection([
            '_native_api' => false,
        ]))
        ->setDestination(new Collection([
            'id' => 'int_test123',
            'next_action' => [
                'type' => 'redirect',
                'url' => 'https://pay.example.com/redirect',
            ],
        ]));

    $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination()->get('next_action_type'))->toEqual('redirect');
    expect($result->getDestination()->get('pay_url'))->toEqual('https://pay.example.com/redirect');
});

it('渲染二维码归一化支付 URL', function () {
    $rocket = (new Rocket())
        ->setPayload(new Collection([
            '_native_api' => false,
        ]))
        ->setDestination(new Collection([
            'id' => 'int_test456',
            'next_action' => [
                'type' => 'render_qrcode',
                'qrcode_url' => 'weixin://wxpay/bizpayurl?pr=test',
            ],
        ]));

    $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination()->get('next_action_type'))->toEqual('render_qrcode');
    expect($result->getDestination()->get('pay_url'))->toEqual('weixin://wxpay/bizpayurl?pr=test');
});

it('未知动作归一化支付 URL', function () {
    $rocket = (new Rocket())
        ->setPayload(new Collection([
            '_native_api' => false,
        ]))
        ->setDestination(new Collection([
            'id' => 'int_test789',
            'next_action' => [
                'type' => 'display_details',
            ],
        ]));

    $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination()->get('next_action_type'))->toEqual('display_details');
    expect($result->getDestination()->get('pay_url'))->toEqual('');
});

it('native api 确认保留创建时的 payload 字段', function () {
    $tokenResponse = new Response(201, [], json_encode([
        'token' => 'native_airwallex_token',
        'expires_at' => gmdate('Y-m-d\\TH:i:sO', time() + 1800),
    ]));
    $confirmResponse = new Response(201, [], json_encode([
        'id' => 'int_native_123',
        'status' => 'REQUIRES_CUSTOMER_ACTION',
        'next_action' => [
            'type' => 'redirect',
            'url' => 'https://pay.example.com/native-redirect',
        ],
        'return_url' => 'https://pay.yansongda.cn/native-return',
    ]));

    $http = Mockery::mock(Client::class);
    $http->shouldReceive('sendRequest')->andReturn($tokenResponse, $confirmResponse);
    Pay::set(HttpClientInterface::class, $http);

    $rocket = (new Rocket())
        ->setParams([
            'amount' => 100,
            'currency' => 'USD',
            'merchant_order_id' => 'order_native_123',
        ])
        ->setPayload(new Collection([
            '_native_api' => true,
            'request_id' => 'req_native_123',
            'return_url' => 'https://pay.yansongda.cn/native-return',
            'payment_method' => [
                'type' => 'wechatpay',
                'wechatpay' => [
                    'flow' => 'qrcode',
                ],
            ],
        ]))
        ->setDestination(new Collection([
            'id' => 'int_native_123',
            'client_secret' => 'cs_native_123',
        ]));

    $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getParams()['payment_intent_id'])->toEqual('int_native_123');
    expect($result->getDestination()->get('next_action_type'))->toEqual('redirect');
    expect($result->getDestination()->get('pay_url'))->toEqual('https://pay.example.com/native-redirect');
    expect($result->getDestination()->get('return_url'))->toEqual('https://pay.yansongda.cn/native-return');
});

it('native api 确认使用租户配置', function () {
    $tokenResponse = new Response(201, [], json_encode([
        'token' => 'secondary_airwallex_token',
        'expires_at' => gmdate('Y-m-d\\TH:i:sO', time() + 1800),
    ]));
    $confirmResponse = new Response(201, [], json_encode([
        'id' => 'int_secondary_123',
        'next_action' => [
            'type' => 'redirect',
            'url' => 'https://pay.example.com/secondary-redirect',
        ],
    ]));

    $http = Mockery::mock(HttpClientInterface::class);
    $http->shouldReceive('sendRequest')
        ->once()
        ->ordered()
        ->withArgs(function (RequestInterface $request): bool {
            expect($request->getHeaderLine('x-client-id'))->toBe('airwallex_secondary_client_id');
            expect($request->getHeaderLine('x-api-key'))->toBe('airwallex_secondary_api_key');

            return true;
        })
        ->andReturn($tokenResponse);
    $http->shouldReceive('sendRequest')
        ->once()
        ->ordered()
        ->withArgs(function (RequestInterface $request): bool {
            expect($request->getHeaderLine('Authorization'))->toBe('Bearer secondary_airwallex_token');

            return true;
        })
        ->andReturn($confirmResponse);
    Pay::set(HttpClientInterface::class, $http);

    $rocket = (new Rocket())
        ->setParams([
            '_config' => 'secondary',
            'amount' => 100,
            'currency' => 'USD',
            'merchant_order_id' => 'order_secondary_123',
        ])
        ->setPayload(new Collection([
            '_native_api' => true,
            'request_id' => 'req_secondary_123',
            'return_url' => 'https://pay.yansongda.cn/airwallex/secondary-return',
        ]))
        ->setDestination(new Collection([
            'id' => 'int_secondary_123',
        ]));

    $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getParams()['_config'])->toBe('secondary');
    expect($result->getParams()['payment_intent_id'])->toBe('int_secondary_123');
    expect($result->getDestination()->get('pay_url'))->toBe('https://pay.example.com/secondary-redirect');
});

it('destination 非 Collection 时跳过', function () {
    $rocket = (new Rocket())
        ->setPayload(new Collection([
            '_native_api' => true,
        ]))
        ->setDestination(new Response(200, [], '{"id":"int_resp_123"}'));

    $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination())->toBeInstanceOf(Response::class);
});

it('destination 非 Collection 时跳过归一化', function () {
    $rocket = (new Rocket())
        ->setPayload(new Collection([
            '_native_api' => false,
        ]))
        ->setDestination(new Response(200, [], '{"id":"int_resp_456"}'));

    $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

    expect($result->getDestination())->toBeInstanceOf(Response::class);
});

