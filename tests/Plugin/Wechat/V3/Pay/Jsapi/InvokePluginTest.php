<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Pay\Jsapi;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\InvokePlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class InvokePluginTest extends TestCase
{
    protected InvokePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new InvokePlugin();
    }

    public function testMissingPrepayId()
    {
        $rocket = new Rocket();

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_MISSING_NECESSARY_PARAMS);
        self::expectExceptionMessage('预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = (new Rocket())
            ->setDestination(new Collection(['prepay_id' => 'yansongda']))
            ->setPayload(['_invoke_appid' => '111']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('paySign', $contents->all());
        self::assertArrayHasKey('timeStamp', $contents->all());
        self::assertArrayHasKey('nonceStr', $contents->all());
        self::assertEquals('111', $contents->get('appId'));
        self::assertEquals('prepay_id=yansongda', $contents->get('package'));
        self::assertEquals('RSA', $contents->get('signType'));
    }

    public function testNormal()
    {
        $rocket = (new Rocket())->setDestination(new Collection(['prepay_id' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('paySign', $contents->all());
        self::assertArrayHasKey('timeStamp', $contents->all());
        self::assertArrayHasKey('nonceStr', $contents->all());
        self::assertEquals('wx55955316af4ef13', $contents->get('appId'));
        self::assertEquals('prepay_id=yansongda', $contents->get('package'));
        self::assertEquals('RSA', $contents->get('signType'));
    }

    public function testServiceParams()
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'service_provider4'])
            ->setDestination(new Collection(['prepay_id' => 'yansongda']))
            ->setPayload(['_invoke_appid' => '111']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('paySign', $contents->all());
        self::assertArrayHasKey('timeStamp', $contents->all());
        self::assertArrayHasKey('nonceStr', $contents->all());
        self::assertEquals('111', $contents->get('appId'));
        self::assertEquals('prepay_id=yansongda', $contents->get('package'));
        self::assertEquals('RSA', $contents->get('signType'));
    }

    public function testService()
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'service_provider'])
            ->setDestination(new Collection(['prepay_id' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('paySign', $contents->all());
        self::assertArrayHasKey('timeStamp', $contents->all());
        self::assertArrayHasKey('nonceStr', $contents->all());
        self::assertEquals('wx55955316af4ef15', $contents->get('appId'));
        self::assertEquals('prepay_id=yansongda', $contents->get('package'));
        self::assertEquals('RSA', $contents->get('signType'));
    }
}