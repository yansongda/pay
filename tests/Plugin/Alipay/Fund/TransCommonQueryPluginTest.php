<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Fund;

use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Fund\TransCommonQueryPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class TransCommonQueryPluginTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TransCommonQueryPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
        $payloadString = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseParser::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.trans.common.query', $payloadString);
        self::assertStringContainsString('TRANS_ACCOUNT_NO_PWD', $payloadString);
    }
}
