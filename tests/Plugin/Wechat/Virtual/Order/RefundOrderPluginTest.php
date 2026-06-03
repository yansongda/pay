<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Order;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Order\RefundOrderPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundOrderPluginTest extends TestCase
{
    protected RefundOrderPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundOrderPlugin();
    }

    public function testWithOrderId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'order_id' => '123456',
            'refund_order_id' => 'REFUND_001',
            'left_fee' => 100,
            'refund_fee' => 50,
            'biz_meta' => 'test_meta',
            'refund_reason' => '1',
            'req_from' => '2',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('POST', $result->getPayload()->get('_method'));
        self::assertEquals('xpay/refund_order', $result->getPayload()->get('_url'));
        self::assertEquals('test_openid', $result->getPayload()->get('openid'));
        self::assertEquals('123456', $result->getPayload()->get('order_id'));
        self::assertNull($result->getPayload()->get('wx_order_id'));
        self::assertEquals('REFUND_001', $result->getPayload()->get('refund_order_id'));
        self::assertEquals(100, $result->getPayload()->get('left_fee'));
        self::assertEquals(50, $result->getPayload()->get('refund_fee'));
        self::assertEquals('test_meta', $result->getPayload()->get('biz_meta'));
        self::assertEquals('1', $result->getPayload()->get('refund_reason'));
        self::assertEquals('2', $result->getPayload()->get('req_from'));
    }

    public function testWithWxOrderId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'wx_order_id' => 'wx_123456',
            'refund_order_id' => 'REFUND_001',
            'left_fee' => 100,
            'refund_fee' => 50,
            'refund_reason' => '1',
            'req_from' => '2',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNull($result->getPayload()->get('order_id'));
        self::assertEquals('wx_123456', $result->getPayload()->get('wx_order_id'));
    }

    public function testWithAccessToken()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'openid' => 'test_openid',
            'order_id' => '123456',
            'refund_order_id' => 'REFUND_001',
            'left_fee' => 100,
            'refund_fee' => 50,
            'refund_reason' => '1',
            'req_from' => '2',
            'access_token' => 'test_token',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('test_token', $result->getPayload()->get('access_token'));
    }
}
