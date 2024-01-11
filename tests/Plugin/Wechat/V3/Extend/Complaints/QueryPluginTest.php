<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Extend\Complaints;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\Complaints\QueryPlugin;
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

    public function testEmptyComplaintId()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 查询投诉单列表，缺少必要参数');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $payload = [
            'limit' => 2,
            'offset' => 3,
            'begin_date' => '2021-06-06',
            'end_date' => '2021-06-07',
        ];

        $rocket = new Rocket();
        $rocket->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/merchant-service/complaints-v2?limit=2&offset=3&begin_date=2021-06-06&end_date=2021-06-07',
            '_service_url' => 'v3/merchant-service/complaints-v2?limit=2&offset=3&begin_date=2021-06-06&end_date=2021-06-07',
        ], $result->getPayload()->all());
    }
}
