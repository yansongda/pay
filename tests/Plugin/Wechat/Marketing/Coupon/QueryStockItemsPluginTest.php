<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Marketing\Coupon;

use GuzzleHttp\Psr7\Uri;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\QueryStockItemsPlugin;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryStockItemsPluginTest extends TestCase
{
    protected QueryStockItemsPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryStockItemsPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection([
            'stock_id' => '123456',
            'limit' => 1,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });

        $radar = $result->getRadar();

        self::assertEquals('GET', $radar->getMethod());
        self::assertNull($result->getPayload());
        self::assertEquals(new Uri(Wechat::URL[Pay::MODE_NORMAL].'v3/marketing/favor/stocks/123456/items?limit=1&stock_creator_mchid=1600314069'), $radar->getUri());
    }

    public function testException()
    {
        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::MISSING_NECESSARY_PARAMS);

        $rocket = (new Rocket())->setParams([])->setPayload(new Collection());

        $this->plugin->assembly($rocket, function ($rocket) {return $rocket; });
    }
}
