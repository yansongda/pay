<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Wechat\Virtual\Goods;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\Virtual\Goods\StartUploadGoodsPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class StartUploadGoodsPluginTest extends TestCase
{
    protected StartUploadGoodsPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new StartUploadGoodsPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 微信虚拟支付批量上传道具，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'group_id' => 'test_group_001',
            'goods_list' => [
                ['goods_id' => 'g001', 'goods_name' => '测试道具'],
            ],
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('POST', $payload->get('_method'));
        self::assertEquals('/xpay/start_upload_goods', $payload->get('_url'));
        self::assertEquals(0, $payload->get('env'));
        self::assertEquals('test_group_001', $payload->get('group_id'));
        self::assertEquals([
            ['goods_id' => 'g001', 'goods_name' => '测试道具'],
        ], $payload->get('goods_list'));
    }

    public function testSandboxEnv()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'group_id' => 'test_group_001',
            'goods_list' => [
                ['goods_id' => 'g001', 'goods_name' => '测试道具'],
            ],
            'env' => 1,
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals(1, $payload->get('env'));
        self::assertEquals('/xpay/start_upload_goods', $payload->get('_url'));
    }

    public function testAccessTokenPassedThrough()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'group_id' => 'test_group_001',
            'goods_list' => [
                ['goods_id' => 'g001', 'goods_name' => '测试道具'],
            ],
            '_access_token' => 'test_token',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('test_token', $payload->get('_access_token'));
    }
}
