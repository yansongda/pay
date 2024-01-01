<?php

namespace Plugin\Wechat\V3\Pay\Refund;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Refund\RefundAbnormalPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RefundAbnormalPluginTest extends TestCase
{
    protected RefundAbnormalPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RefundAbnormalPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 发起异常退款，参数缺少 `refund_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'refund_id' => '111',
            "name" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/refund/domestic/refunds/111/apply-abnormal-refund',
            '_service_url' => 'v3/refund/domestic/refunds/111/apply-abnormal-refund',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }

    public function testNormalWithName()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'refund_id' => '111',
            "name" => "yansongda",
            'bank_account' => '222',
            'real_name' => '333',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->all();

        self::assertEquals('POST', $payload['_method']);
        self::assertEquals('v3/refund/domestic/refunds/111/apply-abnormal-refund', $payload['_url']);
        self::assertEquals('v3/refund/domestic/refunds/111/apply-abnormal-refund', $payload['_service_url']);
        self::assertEquals('yansongda', $payload['name']);
        self::assertArrayHasKey('bank_account', $payload);
        self::assertNotEquals('222', $payload['bank_account']);
        self::assertArrayHasKey('real_name', $payload);
        self::assertNotEquals('333', $payload['real_name']);
        self::assertArrayHasKey('_serial_no', $payload);
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            'sub_mchid' => '111',
            'refund_id' => '222',
            'name' => 'yansongda',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/refund/domestic/refunds/222/apply-abnormal-refund',
            '_service_url' => 'v3/refund/domestic/refunds/222/apply-abnormal-refund',
            'sub_mchid' => '111',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            'refund_id' => '222',
            'name' => 'yansongda',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/refund/domestic/refunds/222/apply-abnormal-refund',
            '_service_url' => 'v3/refund/domestic/refunds/222/apply-abnormal-refund',
            'sub_mchid' => '1600314070',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }

    public function testServiceWithName()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'refund_id' => '111',
            "name" => "yansongda",
            'bank_account' => '222',
            'real_name' => '333',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->all();

        self::assertEquals('POST', $payload['_method']);
        self::assertEquals('v3/refund/domestic/refunds/111/apply-abnormal-refund', $payload['_url']);
        self::assertEquals('v3/refund/domestic/refunds/111/apply-abnormal-refund', $payload['_service_url']);
        self::assertEquals('1600314070', $payload['sub_mchid']);
        self::assertEquals('yansongda', $payload['name']);
        self::assertArrayHasKey('bank_account', $payload);
        self::assertNotEquals('222', $payload['bank_account']);
        self::assertArrayHasKey('real_name', $payload);
        self::assertNotEquals('333', $payload['real_name']);
        self::assertArrayHasKey('_serial_no', $payload);
    }
}
