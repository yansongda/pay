<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Trade\Pay;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Douyin\V1\Trade\Pay\QueryPlugin;
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

    public function testEmptyPayload(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'trade']);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 抖音交易系统-查询订单，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'trade'])->setPayload(new Collection([
            'out_order_no' => 'test_order_001',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('GET', $payload->get('_method'));
        self::assertEquals('api/trade/v1/query_order', $payload->get('_url'));
        self::assertEquals('tt_trade_app_id', $payload->get('app_id'));
    }
}
