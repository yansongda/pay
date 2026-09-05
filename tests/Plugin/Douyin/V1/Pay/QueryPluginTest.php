<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Douyin\V1\Pay;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Douyin\V1\Pay\QueryPlugin;
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

    public function testNormal(): void
    {
        $rocket = new Rocket();
        $rocket->setParams([]);
        $rocket->setPayload(new Collection(['order_id' => 'ot123456']));

        $result = $this->plugin->assembly($rocket, fn ($rocket) => $rocket);
        $payload = $result->getPayload();

        self::assertSame('POST', $payload?->get('_method'));
        self::assertSame('/api/trade_basic/v1/developer/order_query/', $payload?->get('_url'));
        // 业务字段透传保留，除 `_method`/`_url` 外不新增任何键（尤其无 app_id）
        self::assertSame([
            'order_id' => 'ot123456',
            '_method' => 'POST',
            '_url' => '/api/trade_basic/v1/developer/order_query/',
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
            '_url' => '/api/trade_basic/v1/developer/order_query/',
        ], $payload?->all());
    }
}
