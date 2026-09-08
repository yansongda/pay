<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryCpsPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryCpsPluginTest extends TestCase
{
    protected QueryCpsPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryCpsPlugin();
    }

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);
        $rocket->setPayload(new Collection(['out_order_no' => '20260905123456']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertSame('POST', $payload?->get('_method'));
        self::assertSame('/api/trade_basic/v1/developer/query_cps/', $payload?->get('_url'));
        // 业务字段透传保留，除 `_method`/`_url` 外不新增任何键（尤其无 app_id）
        self::assertSame([
            'out_order_no' => '20260905123456',
            '_method' => 'POST',
            '_url' => '/api/trade_basic/v1/developer/query_cps/',
        ], $payload?->all());
    }

    public function testNullPayload(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertSame([
            '_method' => 'POST',
            '_url' => '/api/trade_basic/v1/developer/query_cps/',
        ], $payload?->all());
    }
}
