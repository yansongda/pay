<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\Coupon;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\QueryDetailPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryDetailPluginTest extends TestCase
{
    protected QueryDetailPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryDetailPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 查询代金券详情，参数缺少 `openid` 或 `coupon_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "openid" => "111",
            'coupon_id' => '222',
            'appid' => '333',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/users/111/coupons/222?appid=333',
            '_service_url' => 'v3/marketing/favor/users/111/coupons/222?appid=333',
        ], $result->getPayload()->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "openid" => "111",
            'coupon_id' => '222',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/users/111/coupons/222?appid=wx55955316af4ef13',
            '_service_url' => 'v3/marketing/favor/users/111/coupons/222?appid=wx55955316af4ef13',
        ], $result->getPayload()->all());
    }
}
