<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\Coupon;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\RestartPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class RestartPluginTest extends TestCase
{
    protected RestartPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new RestartPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 激活代金券，参数缺少 `stock_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "stock_id" => "111",
            'stock_creator_mchid' => '222',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/marketing/favor/stocks/111/restart',
            '_service_url' => 'v3/marketing/favor/stocks/111/restart',
            'stock_creator_mchid' => '222',
        ], $result->getPayload()->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "stock_id" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/marketing/favor/stocks/111/restart',
            '_service_url' => 'v3/marketing/favor/stocks/111/restart',
            'stock_creator_mchid' => '1600314069',
        ], $result->getPayload()->all());
    }
}
