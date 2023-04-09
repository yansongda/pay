<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Mini;

use Yansongda\Pay\Plugin\Wechat\Pay\Mini\InvokePrepayV2Plugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;
use function Yansongda\Pay\get_wechat_config;

class InvokePrepayV2PluginTest extends TestCase
{
    protected InvokePrepayV2Plugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new InvokePrepayV2Plugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())->setDestination(new Collection(['prepay_id' => 'yansongda']));
        $config = get_wechat_config($rocket->getParams());

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('paySign', $contents->all());
        self::assertArrayHasKey('timeStamp', $contents->all());
        self::assertArrayHasKey('nonceStr', $contents->all());
        self::assertEquals('prepay_id=yansongda', $contents->get('package'));
        self::assertEquals('MD5', $contents->get('signType'));
        self::assertEquals($config['mini_app_id'], $contents->get('appId'));
    }
}
