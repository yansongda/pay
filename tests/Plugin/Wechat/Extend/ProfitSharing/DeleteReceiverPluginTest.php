<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Extend\ProfitSharing;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Extend\ProfitSharing\DeleteReceiverPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class DeleteReceiverPluginTest extends TestCase
{
    protected DeleteReceiverPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new DeleteReceiverPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 缺少分账参数');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
            'appid' => '1111',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/profitsharing/receivers/delete',
            '_service_url' => 'v3/profitsharing/receivers/delete',
            'test' => 'yansongda',
            'appid' => '1111',
        ], $result->getPayload()->all());
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
            '_url' => 'v3/profitsharing/receivers/delete',
            '_service_url' => 'v3/profitsharing/receivers/delete',
            'test' => 'yansongda',
            'appid' => 'wx55955316af4ef13',
        ], $result->getPayload()->all());
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "test" => "yansongda",
            'appid' => '1111',
            'sub_mchid' => '2222',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/profitsharing/receivers/delete',
            '_service_url' => 'v3/profitsharing/receivers/delete',
            'test' => 'yansongda',
            'appid' => '1111',
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
            '_url' => 'v3/profitsharing/receivers/delete',
            '_service_url' => 'v3/profitsharing/receivers/delete',
            'test' => 'yansongda',
            'appid' => 'wx55955316af4ef13',
            'sub_mchid' => '1600314070',
        ], $result->getPayload()->all());
    }
}
