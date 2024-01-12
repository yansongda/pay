<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\Coupon\Stock;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryItemsPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryItemsPluginTest extends TestCase
{
    protected QueryItemsPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryItemsPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 查询代金券可用单品，参数缺少 `stock_id`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            "stock_id" => "111",
            'stock_creator_mchid' => '222',
            '_t' => 'a',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/stocks/111/items?stock_creator_mchid=222',
            '_service_url' => 'v3/marketing/favor/stocks/111/items?stock_creator_mchid=222',
        ], $result->getPayload()->all());
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "stock_id" => "111",
            '_t' => 'a',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/marketing/favor/stocks/111/items?stock_creator_mchid=1600314069',
            '_service_url' => 'v3/marketing/favor/stocks/111/items?stock_creator_mchid=1600314069',
        ], $result->getPayload()->all());
    }
}
