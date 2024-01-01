<?php

namespace Plugin\Wechat\V3\Marketing\Transfer;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\CreatePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CreatePluginTest extends TestCase
{
    protected CreatePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CreatePlugin();
    }

    public function testModeWrong()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider']);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);
        self::expectExceptionMessage('参数异常: 发起商家转账，只支持普通商户模式，当前配置为服务商模式');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('发起商家转账参数，参数缺失');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
            'appid' => '1111',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/transfer/batches',
            'test' => 'yansongda',
            'appid' => '1111',
        ], $result->getPayload()->all());
    }

    public function testNormalWithoutName()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/transfer/batches',
            'test' => 'yansongda',
            'appid' => 'wx55955316af4ef13',
        ], $result->getPayload()->all());
    }

    public function testNormalWithName()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            "test" => "111",
            'transfer_detail_list' => [
                [
                    'user_name' => 'yansongda'
                ]
            ]
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) {
            return $rocket;
        });

        $payload = $result->getPayload()->all();
        self::assertEquals('POST', $payload['_method']);
        self::assertEquals('v3/transfer/batches', $payload['_url']);
        self::assertEquals('wx55955316af4ef13', $payload['appid']);
        self::assertEquals('111', $payload['test']);
        self::assertArrayHasKey('_serial_no', $payload);
        self::assertArrayHasKey('user_name', $payload['transfer_detail_list'][0]);
        self::assertNotEquals('yansongda', $payload['transfer_detail_list'][0]['user_name']);
    }
}
