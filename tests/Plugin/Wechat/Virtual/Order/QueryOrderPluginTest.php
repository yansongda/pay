<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Order;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\QueryOrderPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryOrderPluginTest extends TestCase
{
    protected QueryOrderPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryOrderPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付查询订单，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingOpenid()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'env' => 0,
            'order_id' => '123456',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付查询订单，缺少 openid 或 env');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingOrderIds()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => 0,
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付查询订单，需要 order_id 或 wx_order_id');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testWithOrderId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => 0,
            'order_id' => '123456',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/xpay/query_order', $result->getPayload()->get('_url'));
        self::assertEquals('test_openid', $result->getPayload()->get('openid'));
        self::assertEquals('123456', $result->getPayload()->get('order_id'));
        self::assertNull($result->getPayload()->get('wx_order_id'));
    }

    public function testWithWxOrderId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => 0,
            'wx_order_id' => 'wx_123456',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('/xpay/query_order', $result->getPayload()->get('_url'));
        self::assertEquals('test_openid', $result->getPayload()->get('openid'));
        self::assertNull($result->getPayload()->get('order_id'));
        self::assertEquals('wx_123456', $result->getPayload()->get('wx_order_id'));
    }

    public function testWithBothOrderIds()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => 0,
            'order_id' => '123456',
            'wx_order_id' => 'wx_123456',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('123456', $result->getPayload()->get('order_id'));
        self::assertEquals('wx_123456', $result->getPayload()->get('wx_order_id'));
    }

    public function testEnvAsString()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => '0',
            'order_id' => '123456',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('123456', $result->getPayload()->get('order_id'));
    }

    public function testWithAccessToken()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'env' => 0,
            'order_id' => '123456',
            'access_token' => 'test_token',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('test_token', $result->getPayload()->get('access_token'));
    }
}
