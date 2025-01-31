<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Marketing\MchTransfer;

use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\InvokeAndroidPlugin;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class InvokeAndroidPluginTest extends TestCase
{
    protected InvokeAndroidPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new InvokeAndroidPlugin();
    }

    public function testModeWrong()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider']);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);
        self::expectExceptionMessage('参数异常: Android调起用户确认收款，只支持普通商户模式，当前配置为服务商模式');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingPackage()
    {
        $rocket = new Rocket();

        self::expectException(InvalidResponseException::class);
        self::expectExceptionCode(Exception::RESPONSE_MISSING_NECESSARY_PARAMS);
        self::expectExceptionMessage('Android调起用户确认收款失败：响应缺少 `package_info` 参数，请自行检查参数是否符合微信要求');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormalParams()
    {
        $rocket = (new Rocket())
            ->setDestination(new Collection(['package_info' => 'affffddafdfafddffda==']))
            ->setPayload(['_invoke_appId' => '111']);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertEquals('requestMerchantTransfer', $contents->get('businessType'));
        self::assertEquals('appId=111&mchId=1600314069&package=affffddafdfafddffda%3D%3D', $contents->get('query'));
    }

    public function testNormal()
    {
        $rocket = (new Rocket())->setDestination(new Collection(['package_info' => 'affffddafdfafddffda==']));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $contents = $result->getDestination();

        self::assertEquals('requestMerchantTransfer', $contents->get('businessType'));
        self::assertEquals('appId=wx55955316af4ef13&mchId=1600314069&package=affffddafdfafddffda%3D%3D', $contents->get('query'));
    }
}