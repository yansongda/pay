<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Withdraw;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\CreateWithdrawOrderPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CreateWithdrawOrderPluginTest extends TestCase
{
    protected CreateWithdrawOrderPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CreateWithdrawOrderPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付创建提现单，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingWithdrawNo()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'withdraw_amount' => '0.01',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付创建提现单，缺少 withdraw_no');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'withdraw_no' => 'WITHDRAW_001',
            'withdraw_amount' => '0.01',
            'env' => 0,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/xpay/create_withdraw_order', $payload->get('_url'));
        self::assertEquals('WITHDRAW_001', $payload->get('withdraw_no'));
        self::assertEquals('0.01', $payload->get('withdraw_amount'));
    }

    public function testWithoutWithdrawAmount()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'withdraw_no' => 'WITHDRAW_001',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('WITHDRAW_001', $payload->get('withdraw_no'));
        self::assertNull($payload->get('withdraw_amount'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'withdraw_no' => 'WITHDRAW_001',
            'withdraw_amount' => '0.01',
            'env' => 1,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('WITHDRAW_001', $payload->get('withdraw_no'));
    }
}
