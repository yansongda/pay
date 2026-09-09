<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\PayScore;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\PayScore\CreatePlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CreatePluginTest extends TestCase
{
    protected CreatePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CreatePlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 创建支付分订单，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'out_order_no' => '202409101234567890',
            'service_introduction' => '停车服务',
            'time_range' => ['start_time' => '20240910100000'],
            'risk_fund' => ['fund_type' => 'PARKING_FEE', 'amount' => 100],
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => '/v3/payscore/serviceorder',
            'appid' => 'wx55955316af4ef13',
            'service_id' => '12345678901234567890123456789012',
            'notify_url' => 'https://pay.yansongda.cn',
            'out_order_no' => '202409101234567890',
            'service_introduction' => '停车服务',
            'time_range' => ['start_time' => '20240910100000'],
            'risk_fund' => ['fund_type' => 'PARKING_FEE', 'amount' => 100],
        ], $result->getPayload()->all());

        self::assertArrayNotHasKey('mchid', $result->getPayload()->all());
    }

    public function testExplicitParamsOverrideConfig()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'out_order_no' => '202409101234567890',
            'appid' => 'wx55955316af4ef99',
            'service_id' => '12345678901234567890123456789999',
            'notify_url' => 'https://pay.yansongda.cn/payscore/notify',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => '/v3/payscore/serviceorder',
            'appid' => 'wx55955316af4ef99',
            'service_id' => '12345678901234567890123456789999',
            'notify_url' => 'https://pay.yansongda.cn/payscore/notify',
            'out_order_no' => '202409101234567890',
        ], $result->getPayload()->all());
    }

    public function testMiniType()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_type' => 'mini'])->setPayload(new Collection([
            'out_order_no' => '202409101234567890',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => '/v3/payscore/serviceorder',
            'appid' => 'wx55955316af4ef14',
            'service_id' => '12345678901234567890123456789012',
            'notify_url' => 'https://pay.yansongda.cn',
            'out_order_no' => '202409101234567890',
        ], $result->getPayload()->all());
    }

    public function testServiceIdMissing()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'empty_wechat_public_cert'])->setPayload(new Collection([
            'out_order_no' => '202409101234567890',
        ]));

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_WECHAT_SERVICE_ID_MISSING);
        self::expectExceptionMessage('参数异常: 缺少支付分服务ID -- [service_id]');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }
}
