<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Goods;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\QueryPublishGoodsPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class QueryPublishGoodsPluginTest extends TestCase
{
    protected QueryPublishGoodsPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new QueryPublishGoodsPlugin();
    }


    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'publish_task_id' => 'pub_task_789',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('xpay/query_publish_goods', $payload->get('_url'));
        self::assertNull($payload->get('env'));
        self::assertEquals('pub_task_789', $payload->get('publish_task_id'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'publish_task_id' => 'pub_task_789',
            'env' => 1,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('env'));
        self::assertEquals('xpay/query_publish_goods', $payload->get('_url'));
    }
}
