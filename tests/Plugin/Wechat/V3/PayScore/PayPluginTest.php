<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\PayScore;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\PayPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PayPluginTest extends TestCase
{
    protected PayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PayPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 支付分订单催收，参数缺少 `out_order_no`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingOutOrderNo()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['appid' => 'payload_app_id']));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 支付分订单催收，参数缺少 `out_order_no`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingServiceId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
            'out_order_no' => '202609100005',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
        self::expectExceptionMessage('参数异常: 支付分订单催收，参数缺少 `service_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'out_order_no' => '202609100005',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => '/v3/payscore/serviceorder/202609100005/pay',
            'appid' => 'wx55955316af4ef13',
            'service_id' => '12345678901234567890123456789012',
        ], $result->getPayload()->all());
        self::assertFalse($result->getPayload()->has('out_order_no'));
        self::assertFalse($result->getPayload()->has('mchid'));
    }

    public function testNormalWithPayloadAppIdAndServiceId()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'out_order_no' => '202609100005',
            'appid' => 'payload_app_id',
            'service_id' => 'payload_service_id',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            'appid' => 'payload_app_id',
            'service_id' => 'payload_service_id',
            '_method' => 'POST',
            '_url' => '/v3/payscore/serviceorder/202609100005/pay',
        ], $result->getPayload()->all());
        self::assertFalse($result->getPayload()->has('out_order_no'));
        self::assertFalse($result->getPayload()->has('mchid'));
    }
}
