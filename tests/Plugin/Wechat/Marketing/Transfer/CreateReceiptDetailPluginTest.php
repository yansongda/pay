<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\Marketing\Transfer;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\Marketing\Transfer\CreateReceiptDetailPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CreateReceiptDetailPluginTest extends TestCase
{
    protected CreateReceiptDetailPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CreateReceiptDetailPlugin();
    }

    public function testModeWrong()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_config' => 'service_provider']);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE);
        self::expectExceptionMessage('参数异常: 受理转账明细电子回单，只支持普通商户模式，当前配置为服务商模式');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "test" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'POST',
            '_url' => 'v3/transfer-detail/electronic-receipts',
            'test' => 'yansongda',
        ], $result->getPayload()->all());
    }
}
