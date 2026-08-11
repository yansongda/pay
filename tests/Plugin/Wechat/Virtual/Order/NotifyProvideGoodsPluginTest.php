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

    public function testWithOrderId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'order_id' => '1234567890',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/xpay/notify_provide_goods', $payload->get('_url'));
        self::assertEquals('1234567890', $payload->get('order_id'));
        self::assertNull($payload->get('wx_order_id'));
    }

    public function testWithWxOrderId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'wx_order_id' => 'wx_123456',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertNull($payload->get('order_id'));
        self::assertEquals('wx_123456', $payload->get('wx_order_id'));
    }

    public function testWithBothOrderIds()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'order_id' => '1234567890',
            'wx_order_id' => 'wx_123456',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('1234567890', $payload->get('order_id'));
        self::assertEquals('wx_123456', $payload->get('wx_order_id'));
    }

    public function testWithAccessToken()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'order_id' => '1234567890',
            'access_token' => 'test_access_token',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('test_access_token', $payload->get('access_token'));
    }
}
