<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay;

use Yansongda\Pay\Plugin\Alipay\FormatBizContentPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class FormatBizContentPluginTest extends TestCase
{
    protected FormatBizContentPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new FormatBizContentPlugin();
    }

    public function testSignNormal()
    {
        $payload = [
            "biz_content" => ['out_trade_no' => "yansongda-1622986519"],
        ];

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('{"out_trade_no":"yansongda-1622986519"}', $result->getPayload()->get('biz_content'));
    }

    public function testSignUnderlineParams()
    {
        $payload = [
            "biz_content" => ['out_trade_no' => "yansongda-1622986519", '_method' => 'get', '_ignore' => true],
        ];

        $rocket = new Rocket();
        $rocket->setParams([])->setPayload(new Collection($payload));

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertEquals('{"out_trade_no":"yansongda-1622986519"}', $result->getPayload()->get('biz_content'));
    }
}
