<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Trade\Refund;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Refund\RefundPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundPluginTest extends TestCase
{
    protected RefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundPlugin();
    }

    public function testEmptyPayload(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'trade']);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 抖音交易系统-退款，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'trade'])->setPayload(new Collection([
            'out_order_no' => 'test_order_001',
            'out_refund_no' => 'test_refund_001',
            'refund_amount' => 100,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('api/trade/v1/create_refund', $payload->get('_url'));
        self::assertEquals('tt_trade_app_id', $payload->get('app_id'));
        self::assertEquals('https://yansongda.cn/douyin/trade/refund/notify', $payload->get('notify_url'));
        self::assertNotEmpty($payload->get('sign'));
    }

    public function testCustomNotifyUrl(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'trade'])->setPayload(new Collection([
            'out_order_no' => 'test_order_001',
            'out_refund_no' => 'test_refund_001',
            'refund_amount' => 100,
            'notify_url' => 'https://custom.example.com/refund/notify',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('https://custom.example.com/refund/notify', $result->getPayload()->get('notify_url'));
    }
}
