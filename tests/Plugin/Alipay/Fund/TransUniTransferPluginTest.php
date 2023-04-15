<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Fund;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Alipay\Fund\TransUniTransferPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;

class TransUniTransferPluginTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TransUniTransferPlugin();
    }

    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.trans.uni.transfer', $result->getPayload()->toJson());
        self::assertStringContainsString('DIRECT_TRANSFER', $result->getPayload()->toJson());
        self::assertStringContainsString('TRANS_ACCOUNT_NO_PWD', $result->getPayload()->toJson());
    }
}
