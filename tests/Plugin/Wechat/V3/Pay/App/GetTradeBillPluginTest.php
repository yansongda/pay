<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Pay\App;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\App\GetTradeBillPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class GetTradeBillPluginTest extends TestCase
{
    protected GetTradeBillPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GetTradeBillPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: App 申请交易账单，参数为空');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "download_url" => "111",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/bill/tradebill?download_url=111',
            '_service_url' => 'v3/bill/tradebill?download_url=111',
        ], $result->getPayload()->all());
    }
}
