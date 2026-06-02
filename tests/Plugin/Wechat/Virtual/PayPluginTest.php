<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\PayPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }


    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'buyQuantity' => 1,
            'productId' => 'test_product',
            'goodsPrice' => 10,
            'outTradeNo' => '20240101000000',
            'attach' => 'custom_attach',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('requestVirtualPayment', $payload->get('_url'));
        self::assertEquals('1234567890', $payload->get('offerId'));
        self::assertEquals(1, $payload->get('buyQuantity'));
        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('CNY', $payload->get('currencyType'));
        self::assertEquals('test_product', $payload->get('productId'));
        self::assertEquals(10, $payload->get('goodsPrice'));
        self::assertEquals('20240101000000', $payload->get('outTradeNo'));
        self::assertEquals('custom_attach', $payload->get('attach'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'buyQuantity' => 1,
            'env' => 1,
            'productId' => 'test_product',
            'goodsPrice' => 10,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('env'));
        self::assertEquals('requestVirtualPayment', $payload->get('_url'));
    }

    public function testCustomUrlOverridesDefault()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            '_url' => '/xpay/pay_deposit',
            'buyQuantity' => 1,
            'productId' => 'test_product',
            'goodsPrice' => 10,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('/xpay/pay_deposit', $result->getPayload()->get('_url'));
    }

    public function testDefaultValues()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'buyQuantity' => 1,
            'productId' => 'test_product',
            'goodsPrice' => 10,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('CNY', $payload->get('currencyType'));
        self::assertEmpty($payload->get('outTradeNo'));
        self::assertEmpty($payload->get('attach'));
    }
}
