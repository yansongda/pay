<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Refund;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\QueryRefundPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryRefundPluginTest extends TestCase
{
    protected QueryRefundPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryRefundPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'refund_id' => '7016089888760043861',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertSame('POST', $payload->get('_method'));
        self::assertSame('/api/trade_basic/v1/developer/refund_query/', $payload->get('_url'));
        // 透传字段保留
        self::assertSame('7016089888760043861', $payload->get('refund_id'));
        // 无注入键：业务接口均无 app_id/notify_url 字段，不得注入
        self::assertSame(['refund_id', '_method', '_url'], array_keys($payload->all()));
    }

    public function testOutRefundNoAndOrderId(): void
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'out_refund_no' => '202408040747147327',
            'order_id' => '7016089888760043860',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertSame('202408040747147327', $payload->get('out_refund_no'));
        self::assertSame('7016089888760043860', $payload->get('order_id'));
        self::assertArrayNotHasKey('app_id', $payload->all());
        self::assertArrayNotHasKey('notify_url', $payload->all());
    }

    public function testEmptyPayload(): void
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 抖音查询退款，缺少必要的业务参数');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }

    public function testOnlyFilteredParamsPayload(): void
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            '_foo' => 'bar',
            'refund_id' => null,
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 抖音查询退款，缺少必要的业务参数');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
