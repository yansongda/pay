<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\ECommerceBalance;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceBalance\QueryPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryPluginTest extends TestCase
{
    protected QueryPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPlugin();
    }

    public function testModeWrong()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_SERVICE_MODE);
        self::expectExceptionMessage('参数异常: 查询电商平台账户实时余额，只支持服务商模式，当前配置为普通商户模式');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider']);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 查询电商平台账户实时余额，参数缺少 `account_type`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])
            ->setPayload(new Collection( [
                "account_type" => "111",
                'aaa' => '222',
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_service_url' => 'v3/merchant/fund/balance/111',
        ], $result->getPayload()->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])
            ->setPayload(new Collection( [
                "account_type" => "111",
            ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_service_url' => 'v3/merchant/fund/balance/111',
        ], $result->getPayload()->all());
    }
}
