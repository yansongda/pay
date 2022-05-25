<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\App;

use Yansongda\Pay\Plugin\Wechat\Pay\App\InvokePrepayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class InvokePrepayPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\Pay\App\InvokePrepayPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new InvokePrepayPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())->setDestination(new Collection(['prepay_id' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('appid', $contents->all());
        self::assertArrayHasKey('partnerid', $contents->all());
        self::assertArrayHasKey('package', $contents->all());
        self::assertEquals('Sign=WXPay', $contents->get('package'));
        self::assertArrayHasKey('sign', $contents->all());
        self::assertArrayHasKey('timestamp', $contents->all());
        self::assertArrayHasKey('noncestr', $contents->all());
    }
}
