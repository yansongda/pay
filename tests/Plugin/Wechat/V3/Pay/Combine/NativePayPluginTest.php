<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Pay\Combine;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\NativePayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class NativePayPluginTest extends TestCase
{
    protected NativePayPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new NativePayPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: Native合单 下单，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            'combine_mchid' => '333',
            'combine_appid' => 'yansongdaaa',
            'notify_url' => '444',
            'name' => 'yansongda',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/combine-transactions/native',
            '_service_url' => 'v3/combine-transactions/native',
            "combine_appid" => "yansongdaaa",
            'combine_mchid' => '333',
            'notify_url' => '444',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            'name' => 'yansongda',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/combine-transactions/native',
            '_service_url' => 'v3/combine-transactions/native',
            "combine_appid" => "wx55955316af4ef13",
            'combine_mchid' => '1600314069',
            'notify_url' => 'https://pay.yansongda.cn',
            'name' => 'yansongda',
        ], $result->getPayload()->all());
    }
}
