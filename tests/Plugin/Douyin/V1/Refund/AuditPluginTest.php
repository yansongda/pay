<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Refund;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Refund\AuditPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AuditPluginTest extends TestCase
{
    protected AuditPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AuditPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'refund_id' => '7016089888760043861',
            'refund_audit_status' => 1,
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertSame('POST', $payload->get('_method'));
        self::assertSame('/api/trade_basic/v1/developer/refund_audit_callback/', $payload->get('_url'));
        // 透传字段保留
        self::assertSame('7016089888760043861', $payload->get('refund_id'));
        self::assertSame(1, $payload->get('refund_audit_status'));
        // 无注入键：业务接口均无 app_id/notify_url 字段，不得注入
        self::assertSame(['refund_id', 'refund_audit_status', '_method', '_url'], array_keys($payload->all()));
    }

    public function testDenyMessagePassThrough(): void
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'refund_id' => '7016089888760043861',
            'refund_audit_status' => 2,
            'deny_message' => '不支持退款',
        ]));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        // deny_message 仅透传，不做必填强校验（官方为服务端校验）
        self::assertSame('不支持退款', $payload->get('deny_message'));
        self::assertSame(2, $payload->get('refund_audit_status'));
    }

    public function testEmptyPayload(): void
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 抖音退款审核，缺少必要的业务参数');

        $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
    }
}
