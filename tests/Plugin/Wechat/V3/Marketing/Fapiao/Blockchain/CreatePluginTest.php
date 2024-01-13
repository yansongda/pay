<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain;

use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\CreatePlugin;
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

    public function testNormalWithoutName()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload()->all();

        self::assertEquals('POST', $payload['_method']);
        self::assertEquals('v3/new-tax-control-fapiao/fapiao-applications', $payload['_url']);
        self::assertEquals('yansongda', $payload['test']);
        self::assertArrayHasKey('_serial_no', $payload);
    }

    public function testNormalWithSensitiveData()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
            'buyer_information' => [
                'phone' => '123',
                'email' => '456',
            ]
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payload = $result->getPayload()->all();

        self::assertEquals('POST', $payload['_method']);
        self::assertEquals('v3/new-tax-control-fapiao/fapiao-applications', $payload['_url']);
        self::assertEquals('yansongda', $payload['test']);
        self::assertArrayHasKey('_serial_no', $payload);
        self::assertNotEquals('123', $payload['buyer_information']['phone']);
        self::assertNotEquals('456', $payload['buyer_information']['email']);
    }
}
