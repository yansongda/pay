<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Pay\Combine;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\AppInvokePlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AppInvokePluginTest extends TestCase
{
    protected AppInvokePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AppInvokePlugin();
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
            ->setPayload(['_invoke_appid' => '111', '_invoke_partnerid' => '222']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('sign', $contents->all());
        self::assertArrayHasKey('timestamp', $contents->all());
        self::assertArrayHasKey('noncestr', $contents->all());
        self::assertEquals('111', $contents->get('appid'));
        self::assertEquals('Sign=WXPay', $contents->get('package'));
        self::assertEquals('222', $contents->get('partnerid'));
        self::assertEquals('yansongda', $contents->get('prepayid'));
    }

    public function testNormal()
    {
        $rocket = (new Rocket())->setDestination(new Collection(['prepay_id' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('sign', $contents->all());
        self::assertArrayHasKey('timestamp', $contents->all());
        self::assertArrayHasKey('noncestr', $contents->all());
        self::assertEquals('yansongda', $contents->get('appid'));
        self::assertEquals('Sign=WXPay', $contents->get('package'));
        self::assertEquals('1600314069', $contents->get('partnerid'));
        self::assertEquals('yansongda', $contents->get('prepayid'));
    }

    public function testServiceParams()
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'service_provider4'])
            ->setDestination(new Collection(['prepay_id' => 'yansongda']))
            ->setPayload(['_invoke_appid' => '111', '_invoke_partnerid' => '222']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('sign', $contents->all());
        self::assertArrayHasKey('timestamp', $contents->all());
        self::assertArrayHasKey('noncestr', $contents->all());
        self::assertEquals('111', $contents->get('appid'));
        self::assertEquals('Sign=WXPay', $contents->get('package'));
        self::assertEquals('222', $contents->get('partnerid'));
        self::assertEquals('yansongda', $contents->get('prepayid'));
    }

    public function testService()
    {
        $rocket = (new Rocket())
            ->setParams(['_config' => 'service_provider4'])
            ->setDestination(new Collection(['prepay_id' => 'yansongda']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertArrayHasKey('sign', $contents->all());
        self::assertArrayHasKey('timestamp', $contents->all());
        self::assertArrayHasKey('noncestr', $contents->all());
        self::assertEquals('wx55955316af4ef16', $contents->get('appid'));
        self::assertEquals('Sign=WXPay', $contents->get('package'));
        self::assertEquals('1600314069', $contents->get('partnerid'));
        self::assertEquals('yansongda', $contents->get('prepayid'));
    }
}