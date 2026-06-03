<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Goods;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\StartPublishGoodsPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class StartPublishGoodsPluginTest extends TestCase
{
    protected StartPublishGoodsPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new StartPublishGoodsPlugin();
    }


    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'group_id' => 'test_group_001',
            'upload_task_id' => 'task_123456',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('xpay/start_publish_goods', $payload->get('_url'));
        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('test_group_001', $payload->get('group_id'));
        self::assertEquals('task_123456', $payload->get('upload_task_id'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'group_id' => 'test_group_001',
            'upload_task_id' => 'task_123456',
            'env' => 1,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('env'));
        self::assertEquals('xpay/start_publish_goods', $payload->get('_url'));
    }
}
