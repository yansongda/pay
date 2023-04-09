<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Papay;

use Yansongda\Pay\Plugin\Wechat\Papay\OnlyContractPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

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
        $rocket = (new Rocket())->setParams([])->setPayload(new Collection());

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
        $rocket = (new Rocket())->setParams(['_type' => 'mini'])->setPayload(new Collection());

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('1600314069', $payload->get('mch_id'));
        self::assertEquals('wx55955316af4ef14', $payload->get('appid'));
        self::assertArrayHasKey('notify_url', $payload->all());
        self::assertArrayHasKey('sign', $payload->all());

        // app
        $rocket = (new Rocket())->setParams(['_type' => 'app'])->setPayload(new Collection());

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('1600314069', $payload->get('mch_id'));
        self::assertEquals('yansongda', $payload->get('appid'));
        self::assertArrayHasKey('notify_url', $payload->all());
        self::assertArrayHasKey('sign', $payload->all());
    }
}
