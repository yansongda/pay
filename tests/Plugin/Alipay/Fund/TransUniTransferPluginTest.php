<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Fund;

use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Fund\TransUniTransferPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class TransUniTransferPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new TransUniTransferPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseParser::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.trans.uni.transfer', $result->getPayload()->toJson());
        self::assertStringContainsString('DIRECT_TRANSFER', $result->getPayload()->toJson());
        self::assertStringContainsString('TRANS_ACCOUNT_NO_PWD', $result->getPayload()->toJson());
    }
}
