<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\PayScore;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\CancelPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CancelPluginTest extends TestCase
{
    protected CancelPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CancelPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 取消支付分订单，参数缺少 `out_order_no`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingOutOrderNo()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection(['reason' => '测试取消']));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 取消支付分订单，参数缺少 `out_order_no`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingServiceId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
            'out_order_no' => '202609100001',
            'reason' => '测试取消',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
        self::expectExceptionMessage('参数异常: 取消支付分订单，参数缺少 `service_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'out_order_no' => '202609100001',
            'reason' => '测试取消',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            'reason' => '测试取消',
            '_method' => 'POST',
            '_url' => '/v3/payscore/serviceorder/202609100001/cancel',
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
            'out_order_no' => '202609100001',
            'appid' => 'payload_app_id',
            'service_id' => 'payload_service_id',
            'reason' => '测试取消',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            'appid' => 'payload_app_id',
            'service_id' => 'payload_service_id',
            'reason' => '测试取消',
            '_method' => 'POST',
            '_url' => '/v3/payscore/serviceorder/202609100001/cancel',
        ], $result->getPayload()->all());
        self::assertFalse($result->getPayload()->has('out_order_no'));
        self::assertFalse($result->getPayload()->has('mchid'));
    }
}
