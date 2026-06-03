<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Subscribe;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\SubmitSubscribePayOrderPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class SubmitSubscribePayOrderPluginTest extends TestCase
{
    protected SubmitSubscribePayOrderPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new SubmitSubscribePayOrderPlugin();
    }


    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'contract_id' => 'test_contract_id',
            'amount' => 100,
            'order_id' => 'test_order_id',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('xpay/submit_subscribe_pay_order', $payload->get('_url'));
        self::assertEquals('test_openid', $payload->get('openid'));
        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('test_contract_id', $payload->get('contract_id'));
        self::assertEquals(100, $payload->get('amount'));
        self::assertEquals('test_order_id', $payload->get('order_id'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => 1,
            'contract_id' => 'test_contract_id',
            'amount' => 100,
            'order_id' => 'test_order_id',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('env'));
    }

    public function testDefaultValues()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'contract_id' => 'test_contract_id',
            'amount' => 100,
            'order_id' => 'test_order_id',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('xpay/submit_subscribe_pay_order', $payload->get('_url'));
    }
}
