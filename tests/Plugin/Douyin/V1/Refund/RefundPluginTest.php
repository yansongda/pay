<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Refund;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\RefundPlugin;
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

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'order_id' => '7016089888760043860',
            'out_refund_no' => '202408040747147327',
            'refund_reason' => [['code' => 1, 'text' => '测试退款']],
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertSame('POST', $payload->get('_method'));
        self::assertSame('/api/trade_basic/v1/developer/refund_create/', $payload->get('_url'));
        // payload 无 notify_url 时，缺省注入 config 的 refund_notify_url
        self::assertSame('https://yansongda.cn/douyin/notify', $payload->get('notify_url'));
        // 业务字段透传保留
        self::assertSame('7016089888760043860', $payload->get('order_id'));
        self::assertSame('202408040747147327', $payload->get('out_refund_no'));
        self::assertSame([['code' => 1, 'text' => '测试退款']], $payload->get('refund_reason'));
        // 业务接口均无 app_id 字段，不得注入
        self::assertArrayNotHasKey('app_id', $payload->all());
    }

    public function testExplicitNotifyUrl(): void
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'order_id' => '7016089888760043860',
            'out_refund_no' => '202408040747147327',
            'refund_reason' => [['code' => 1, 'text' => '测试退款']],
            'notify_url' => 'https://explicit.yansongda.cn/douyin/notify',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        // 显式传入 notify_url 优先，不被 config 的 refund_notify_url 覆盖
        self::assertSame('https://explicit.yansongda.cn/douyin/notify', $payload->get('notify_url'));
    }

    public function testEmptyPayload(): void
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 抖音创建退款，缺少必要的业务参数');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testOnlyFilteredParamsPayload(): void
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            '_foo' => 'bar',
            'notify_url' => null,
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 抖音创建退款，缺少必要的业务参数');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
