<?php

namespace Yansongda\Pay\Tests\Plugin\Wechat\V3\Extend\ProfitSharing;

use Yansongda\Artful\Direction\OriginResponseDirection;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing\DownloadBillPlugin;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class DownloadBillPluginTest extends TestCase
{
    protected DownloadBillPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new DownloadBillPlugin();
    }

    public function testEmptyPayload()
    {
        $rocket = new Rocket();

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_NECESSARY_PARAMS_MISSING);
        self::expectExceptionMessage('参数异常: 下载电子回单，参数缺少 `download_url`');

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setPayload(new Collection( [
            "download_url" => "yansongda",
        ]));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals(OriginResponseDirection::class, $result->getDirection());
        self::assertEquals([
            '_method' => 'GET',
            '_url' => 'yansongda',
            '_service_url' => 'yansongda',
        ], $result->getPayload()->all());
    }
}
