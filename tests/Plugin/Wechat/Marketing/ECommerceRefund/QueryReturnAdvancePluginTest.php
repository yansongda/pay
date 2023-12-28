<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Marketing\ECommerceRefund;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Marketing\ECommerceRefund\QueryByWxPlugin;
use Yansongda\Pay\Plugin\Wechat\Marketing\ECommerceRefund\QueryPlugin;
use Yansongda\Pay\Plugin\Wechat\Marketing\ECommerceRefund\QueryReturnAdvancePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryReturnAdvancePluginTest extends TestCase
{
    protected QueryReturnAdvancePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryReturnAdvancePlugin();
    }

    public function testModeWrong()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE);
        self::expectExceptionMessage('参数异常: 平台收付通（退款）-查询垫付回补结果，只支持服务商模式，当前配置为普通商户模式');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider']);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 平台收付通（退款）-查询垫付回补结果，缺少必要参数 `refund_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])
            ->setPayload(new Collection( [
                "refund_id" => "111",
                'sub_mchid' => '222',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_service_url' => 'v3/ecommerce/refunds/111/return-advance?sub_mchid=222',
        ], $result->getPayload()->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])
            ->setPayload(new Collection( [
                "refund_id" => "111",
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_service_url' => 'v3/ecommerce/refunds/111/return-advance?sub_mchid=1600314070',
        ], $result->getPayload()->all());
    }
}
