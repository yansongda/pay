<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Papay;

use Yansongda\Pay\Plugin\Wechat\Papay\OnlyContractPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class OnlyContractPluginTest extends TestCase
{
    protected OnlyContractPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new OnlyContractPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('1600314069', $payload->get('mch_id'));
        self::assertEquals('wx55955316af4ef13', $payload->get('appid'));
        self::assertArrayHasKey('notify_url', $payload->all());
        self::assertArrayHasKey('sign', $payload->all());
    }

    public function testGetConfigKey()
    {
        // mini
        $rocket = new Rocket();
        $rocket->setParams(['_type' => 'mini']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('1600314069', $payload->get('mch_id'));
        self::assertEquals('wx55955316af4ef14', $payload->get('appid'));
        self::assertArrayHasKey('notify_url', $payload->all());
        self::assertArrayHasKey('sign', $payload->all());

        // app
        $rocket = new Rocket();
        $rocket->setParams(['_type' => 'app']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('1600314069', $payload->get('mch_id'));
        self::assertEquals('yansongda', $payload->get('appid'));
        self::assertArrayHasKey('notify_url', $payload->all());
        self::assertArrayHasKey('sign', $payload->all());
    }
}
