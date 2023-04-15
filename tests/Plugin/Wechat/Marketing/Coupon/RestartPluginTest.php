<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Marketing\Coupon;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\RestartPlugin;
use Yansongda\Pay\Provider\Wechat;
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

    public function testNormal()
    {
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
            'stock_id' => '7890',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals('POST', $radar->getMethod());
        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/marketing/favor/stocks/7890/restart'), $radar->getUri());
        self::assertEquals([
            'stock_creator_mchid' => '1600314069',
        ], $result->getPayload()->all());
    }

    public function testException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $rocket = (new Rocket())->setParams([])->setPayload(new Collection());

        $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });
    }

    public function testExistMchId()
    {
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
            'stock_id' => '7890',
            'stock_creator_mchid' => '123',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/marketing/favor/stocks/7890/restart'), $radar->getUri());
        self::assertEquals([
            'stock_creator_mchid' => '123',
        ], $result->getPayload()->all());
    }
}
