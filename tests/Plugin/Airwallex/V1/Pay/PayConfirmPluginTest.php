<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Airwallex\V1\Pay;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Yansongda\Artful\Rocket;
use Yansongda\Artful\Contract\HttpClientInterface;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Airwallex\V1\Pay\PayConfirmPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayConfirmPluginTest extends TestCase
{
    public function testSkipConfirmWhenNativeApiDisabled()
    {
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

        self::assertEquals('int_test123', $result->getDestination()->get('id'));
        self::assertEquals('int_test123', $result->getDestination()->get('payment_intent_id'));
        self::assertEquals('cs_test123', $result->getDestination()->get('client_secret'));
        self::assertEquals('', $result->getDestination()->get('pay_url'));
    }

    public function testNormalizePayUrlFromRedirect()
    {
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

        self::assertEquals('redirect', $result->getDestination()->get('next_action_type'));
        self::assertEquals('https://pay.example.com/redirect', $result->getDestination()->get('pay_url'));
    }

    public function testNormalizePayUrlFromRenderQrcode()
    {
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

        self::assertEquals('render_qrcode', $result->getDestination()->get('next_action_type'));
        self::assertEquals('weixin://wxpay/bizpayurl?pr=test', $result->getDestination()->get('pay_url'));
    }

    public function testNormalizePayUrlFromUnknownAction()
    {
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

        self::assertEquals('display_details', $result->getDestination()->get('next_action_type'));
        self::assertEquals('', $result->getDestination()->get('pay_url'));
    }

    public function testConfirmWithNativeApiKeepsCreatePayloadFields()
    {
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

        self::assertEquals('int_native_123', $result->getParams()['payment_intent_id']);
        self::assertEquals('redirect', $result->getDestination()->get('next_action_type'));
        self::assertEquals('https://pay.example.com/native-redirect', $result->getDestination()->get('pay_url'));
        self::assertEquals('https://pay.yansongda.cn/native-return', $result->getDestination()->get('return_url'));
    }

    public function testSkipWhenDestinationIsNotCollection()
    {
        $rocket = (new Rocket())
            ->setPayload(new Collection([
                '_native_api' => true,
            ]))
            ->setDestination(new Response(200, [], '{"id":"int_resp_123"}'));

        $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

        self::assertInstanceOf(Response::class, $result->getDestination());
    }

    public function testSkipNormalizeWhenDestinationIsNotCollection()
    {
        $rocket = (new Rocket())
            ->setPayload(new Collection([
                '_native_api' => false,
            ]))
            ->setDestination(new Response(200, [], '{"id":"int_resp_456"}'));

        $result = (new PayConfirmPlugin())->assembly($rocket, fn ($rocket) => $rocket);

        self::assertInstanceOf(Response::class, $result->getDestination());
    }
}
