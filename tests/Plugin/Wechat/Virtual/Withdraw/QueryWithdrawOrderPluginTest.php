<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Withdraw;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\QueryWithdrawOrderPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryWithdrawOrderPluginTest extends TestCase
{
    protected QueryWithdrawOrderPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryWithdrawOrderPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付查询提现单，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingWithdrawNo()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'env' => 0,
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付查询提现单，缺少 withdraw_no');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'withdraw_no' => 'WITHDRAW_001',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/xpay/query_withdraw_order', $payload->get('_url'));
        self::assertEquals('WITHDRAW_001', $payload->get('withdraw_no'));
    }

    public function testWithAccessToken()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'withdraw_no' => 'WITHDRAW_001',
            'access_token' => 'test_token',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('test_token', $payload->get('access_token'));
    }
}
