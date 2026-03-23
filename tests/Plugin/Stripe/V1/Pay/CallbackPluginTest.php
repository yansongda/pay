<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Stripe\V1\Pay;

use GuzzleHttp\Psr7\ServerRequest;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Stripe\V1\Pay\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;

class CallbackPluginTest extends TestCase
{
    protected CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testMissingRequestThrowsException()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testInvalidJsonBodyThrowsException()
    {
        $request = new ServerRequest('POST', 'http://localhost', [], 'not-valid-json');

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_INVALID);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalCallback()
    {
        $body = json_encode([
            'id' => 'evt_test123',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test123',
                    'status' => 'succeeded',
                ],
            ],
        ]);

        // localhost skips signature verification in CallbackPlugin
        $request = new ServerRequest('POST', 'http://localhost', [], $body);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEmpty($result->getPayload()->all());
        self::assertNotEmpty($result->getDestination()->all());
        self::assertEquals('evt_test123', $result->getPayload()->get('id'));
        self::assertEquals('payment_intent.succeeded', $result->getPayload()->get('type'));
    }
}
