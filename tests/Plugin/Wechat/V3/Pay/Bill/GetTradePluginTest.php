<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Pay\Bill;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Bill\GetTradePlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class GetTradePluginTest extends TestCase
{
    protected GetTradePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new GetTradePlugin();
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
            '_t' => 'a',
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'v3/bill/tradebill?download_url=111',
            '_service_url' => 'v3/bill/tradebill?download_url=111',
        ], $result->getPayload()->all());
    }
}
