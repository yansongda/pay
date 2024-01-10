<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Extend\ProfitSharing;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\UnfreezePlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class UnfreezePluginTest extends TestCase
{
    protected UnfreezePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new UnfreezePlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 缺少分账参数');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/profitsharing/orders/unfreeze',
            '_service_url' => 'v3/profitsharing/orders/unfreeze',
            'test' => 'yansongda',
        ], $result->getPayload()->all());
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "test" => "yansongda",
            'notify_url' => '1111',
            'sub_mchid' => '2222',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/profitsharing/orders/unfreeze',
            '_service_url' => 'v3/profitsharing/orders/unfreeze',
            'test' => 'yansongda',
            'notify_url' => '1111',
            'sub_mchid' => '2222',
        ], $result->getPayload()->all());
    }

    public function testService()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "test" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/profitsharing/orders/unfreeze',
            '_service_url' => 'v3/profitsharing/orders/unfreeze',
            'test' => 'yansongda',
            'notify_url' => null,
            'sub_mchid' => '1600314070',
        ], $result->getPayload()->all());
    }
}
