<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Order;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\NotifyProvideGoodsPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class NotifyProvideGoodsPluginTest extends TestCase
{
    protected NotifyProvideGoodsPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new NotifyProvideGoodsPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付通知发货，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'oUpF8uEz1xxxxxxxxxx',
            'order_id' => '1234567890',
            'out_trade_no' => '20240101000000',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/xpay/notify_provide_goods', $payload->get('_url'));
        self::assertEquals(0, $payload->get('_env'));
        self::assertEquals('oUpF8uEz1xxxxxxxxxx', $payload->get('openid'));
        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('1234567890', $payload->get('order_id'));
        self::assertEquals('20240101000000', $payload->get('out_trade_no'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'oUpF8uEz1xxxxxxxxxx',
            'env' => 1,
            'order_id' => '1234567890',
            'out_trade_no' => '20240101000000',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('env'));
        self::assertEquals(1, $payload->get('_env'));
    }

    public function testWithAccessToken()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'oUpF8uEz1xxxxxxxxxx',
            'order_id' => '1234567890',
            'out_trade_no' => '20240101000000',
            '_access_token' => 'test_access_token',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('test_access_token', $payload->get('_access_token'));
    }
}
