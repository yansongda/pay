<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Currency;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Currency\CurrencyPayPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CurrencyPayPluginTest extends TestCase
{
    protected CurrencyPayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CurrencyPayPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => 0,
            'user_ip' => '127.0.0.1',
            'amount' => 100,
            'order_id' => 'ORDER_001',
            'payitem' => 'coin*10',
            'remark' => 'purchase coins',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/xpay/currency_pay', $payload->get('_url'));
        self::assertEquals(0, $payload->get('_env'));
        self::assertEquals('test_openid', $payload->get('openid'));
        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('127.0.0.1', $payload->get('user_ip'));
        self::assertEquals(100, $payload->get('amount'));
        self::assertEquals('ORDER_001', $payload->get('order_id'));
        self::assertEquals('coin*10', $payload->get('payitem'));
        self::assertEquals('purchase coins', $payload->get('remark'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => 1,
            'user_ip' => '127.0.0.1',
            'amount' => 100,
            'order_id' => 'ORDER_001',
            'payitem' => 'coin*10',
            'remark' => 'purchase',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('_env'));
        self::assertEquals(1, $payload->get('env'));
    }

    public function testDefaultValues()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'amount' => 100,
            'order_id' => 'ORDER_001',
            'payitem' => 'coin*10',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('', $payload->get('user_ip'));
        self::assertEquals('', $payload->get('remark'));
    }
}
