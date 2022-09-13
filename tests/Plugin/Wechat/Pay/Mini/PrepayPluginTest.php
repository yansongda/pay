<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Mini;

use Yansongda\Pay\Plugin\Wechat\Pay\Mini\PrepayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class PrepayPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\Pay\Mini\PrepayPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new PrepayPlugin();
    }

    public function testWechatIdNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection());

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('wx55955316af4ef14', $payload->get('appid'));
        self::assertEquals('1600314069', $payload->get('mchid'));
    }

    public function testWechatIdNormalWithType()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_type' => 'app'])->setPayload(new Collection());

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('wx55955316af4ef14', $payload->get('appid'));
        self::assertEquals('1600314069', $payload->get('mchid'));
    }

    public function testWechatIdPartner()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection());

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('wx55955316af4ef17', $payload->get('sub_appid'));
        self::assertEquals('1600314070', $payload->get('sub_mchid'));
    }

    public function testWechatIdPartnerDirect()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider'])->setPayload(new Collection(['sub_appid' => '123']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('123', $payload->get('sub_appid'));
        self::assertEquals('wx55955316af4ef14', $payload->get('sp_appid'));
        self::assertEquals('1600314070', $payload->get('sub_mchid'));
        self::assertEquals('1600314069', $payload->get('sp_mchid'));
    }

    public function testWechatIdPartnerDirectMpAppId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider2'])->setPayload(new Collection(['sub_appid' => '123']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertEquals('123', $payload->get('sub_appid'));
        self::assertEquals('wx55955316af4ef18', $payload->get('sp_appid'));
        self::assertEquals('1600314072', $payload->get('sub_mchid'));
        self::assertEquals('1600314071', $payload->get('sp_mchid'));
    }

    public function testWechatIdPartnerWithoutSubAppId()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider3'])->setPayload(new Collection());

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload();

        self::assertArrayNotHasKey('sub_appid', $payload->all());
        self::assertEquals('wx55955316af4ef18', $payload->get('sp_appid'));
        self::assertEquals('1600314072', $payload->get('sub_mchid'));
        self::assertEquals('1600314071', $payload->get('sp_mchid'));
    }
}
