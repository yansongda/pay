<?php

namespace Plugin\Wechat\V3\Extend\ProfitSharing;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\CreatePlugin;
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

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 缺少分账参数');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
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
            '_url' => 'v3/profitsharing/orders',
            '_service_url' => 'v3/profitsharing/orders',
            'test' => 'yansongda',
            'appid' => 'wx55955316af4ef13',
        ], $result->getPayload()->all());
    }

    public function testNormalWithName()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection([
            'receivers' => [
                [
                    'name' => 'yansongda',
                ],
            ],
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) {
            return $rocket;
        });

        $payload = $result->getPayload()->all();
        self::assertEquals('POST', $payload['_method']);
        self::assertEquals('v3/profitsharing/orders', $payload['_url']);
        self::assertEquals('v3/profitsharing/orders', $payload['_service_url']);
        self::assertEquals('wx55955316af4ef13', $payload['appid']);
        self::assertArrayHasKey('_serial_no', $payload);
        self::assertArrayHasKey('name', $payload['receivers'][0]);
        self::assertNotEquals('yansongda', $payload['receivers'][0]['name']);
    }

    public function testServiceParams()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "test" => "yansongda",
            'sub_mchid' => '2222',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/profitsharing/orders',
            '_service_url' => 'v3/profitsharing/orders',
            'test' => 'yansongda',
            'appid' => 'wx55955316af4ef13',
            'sub_mchid' => '2222',
        ], $result->getPayload()->all());
    }

    public function testServiceWithoutName()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "test" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/profitsharing/orders',
            '_service_url' => 'v3/profitsharing/orders',
            'test' => 'yansongda',
            'appid' => 'wx55955316af4ef13',
            'sub_mchid' => '1600314070',
        ], $result->getPayload()->all());
    }

    public function testServiceWithName()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection([
            'receivers' => [
                [
                    'name' => 'yansongda',
                ],
            ],
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) {
            return $rocket;
        });

        $payload = $result->getPayload()->all();
        self::assertEquals('POST', $payload['_method']);
        self::assertEquals('v3/profitsharing/orders', $payload['_url']);
        self::assertEquals('v3/profitsharing/orders', $payload['_service_url']);
        self::assertEquals('wx55955316af4ef13', $payload['appid']);
        self::assertArrayHasKey('_serial_no', $payload);
        self::assertArrayHasKey('name', $payload['receivers'][0]);
        self::assertNotEquals('yansongda', $payload['receivers'][0]['name']);
    }

    public function testWithSubAppId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection( [
            "test" => "yansongda",
            'receivers' => [
                [
                    'type' => 'PERSONAL_SUB_OPENID',
                ],
            ],
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/profitsharing/orders',
            '_service_url' => 'v3/profitsharing/orders',
            'test' => 'yansongda',
            'appid' => 'wx55955316af4ef13',
            'sub_mchid' => '1600314070',
            'sub_appid' => 'wx55955316af4ef15',
            'receivers' => [
                [
                    'type' => 'PERSONAL_SUB_OPENID',
                ],
            ],
        ], $result->getPayload()->all());
    }
}
