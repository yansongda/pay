<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class InvokePrepayPluginTest extends TestCase
{
    /**
     * @var \Yansongda\Pay\Plugin\Wechat\Pay\Common\InvokePrepayPlugin
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

        self::assertArrayHasKey('appId', $contents->all());
        self::assertArrayHasKey('package', $contents->all());
        self::assertArrayHasKey('paySign', $contents->all());
        self::assertArrayHasKey('timeStamp', $contents->all());
        self::assertArrayHasKey('nonceStr', $contents->all());
        self::assertEquals('wx55955316af4ef13', $contents->get('appId'));
    }

    public function testWrongPrepayId()
    {
        $rocket = (new Rocket())->setDestination(new Collection([]));

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_MISSING_NECESSARY_PARAMS);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testPartner()
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'service_provider4'])
            ->setPayload(new Collection(['sub_appid' => '123']))
            ->setDestination(new Collection(['prepay_id' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('appId', $contents->all());
        self::assertArrayHasKey('package', $contents->all());
        self::assertArrayHasKey('paySign', $contents->all());
        self::assertArrayHasKey('timeStamp', $contents->all());
        self::assertArrayHasKey('nonceStr', $contents->all());
        self::assertEquals('123', $contents->get('appId'));
    }
}
