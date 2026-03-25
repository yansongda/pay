<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Bestpay;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Plugin\Bestpay\V1\CallbackPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CallbackPluginTest extends TestCase
{
    protected CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testNormal(): void
    {
        $params = [
            'merchantNo' => 'bestpay_merchant_no',
            'platform' => 'HELIPAY',
            'returnCode' => '0000',
            'returnMsg' => '成功',
            'tradeOrder' => 'test_order_001',
            'totalAmount' => '100',
        ];

        // Build the sign the same way AddPayloadSignPlugin does
        $filtered = array_filter($params, fn ($v) => '' !== $v && null !== $v);
        ksort($filtered);
        $sign = strtolower(md5(http_build_query($filtered).'&key=bestpay_app_key_123456'));

        $params['sign'] = $sign;

        $request = Collection::wrap($params);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);

        self::assertNotEmpty($result->getDestination());
        self::assertEquals('test_order_001', $result->getDestination()->get('tradeOrder'));
    }

    public function testInvalidRequest(): void
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => ['foo' => 'bar']]);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testEmptySign(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        $request = Collection::wrap([
            'merchantNo' => 'bestpay_merchant_no',
            'returnCode' => '0000',
            'sign' => '',
        ]);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testWrongSign(): void
    {
        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $request = Collection::wrap([
            'merchantNo' => 'bestpay_merchant_no',
            'returnCode' => '0000',
            'sign' => 'wrongsignature',
        ]);

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
