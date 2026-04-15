<?php

namespace Plugin\Douyin\V1\Pay\Mini;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\Mini\RefundPlugin;
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

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 抖音小程序退款订单，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            'out_order_no' => '202408040747147327',
            'out_refund_no' => '202408040747147327',
            'reason' => '测试',
            'refund_amount' => 1,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            'out_order_no' => '202408040747147327',
            'out_refund_no' => '202408040747147327',
            'reason' => '测试',
            'refund_amount' => 1,
            '_method' => 'POST',
            '_url' => 'api/apps/ecpay/v1/create_refund',
            'app_id' => 'tt226e54d3bd581bf801',
            'notify_url' => 'https://yansongda.cn/douyin/notify',
        ], $result->getPayload()->all());
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'out_order_no' => '202408040747147327',
            'out_refund_no' => '202408040747147327',
            'reason' => '测试',
            'refund_amount' => 1,
            'thirdparty_id' => 'service_provider111',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            'out_order_no' => '202408040747147327',
            'out_refund_no' => '202408040747147327',
            'reason' => '测试',
            'refund_amount' => 1,
            '_method' => 'POST',
            '_url' => 'api/apps/ecpay/v1/create_refund',
            'app_id' => 'tt226e54d3bd581bf801',
            'thirdparty_id' => 'service_provider111',
            'notify_url' => 'https://yansongda.cn/douyin/notify',
        ], $result->getPayload()->all());
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'out_order_no' => '202408040747147327',
            'out_refund_no' => '202408040747147327',
            'reason' => '测试',
            'refund_amount' => 1,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            'out_order_no' => '202408040747147327',
            'out_refund_no' => '202408040747147327',
            'reason' => '测试',
            'refund_amount' => 1,
            '_method' => 'POST',
            '_url' => 'api/apps/ecpay/v1/create_refund',
            'app_id' => 'tt226e54d3bd581bf801',
            'thirdparty_id' => 'service_provider',
            'notify_url' => 'https://yansongda.cn/douyin/notify',
        ], $result->getPayload()->all());
    }
}
