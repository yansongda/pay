<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\V2\Fund\Transfer\Fund;

use Yansongda\Artful\Direction\ResponseDirection;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund\TransferPlugin;
use Yansongda\Pay\Tests\TestCase;

class TransferPluginTest extends TestCase
{
    protected TransferPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new TransferPlugin();
    }

    public function testNormal()
    {
        $rocket = (new Rocket())
            ->setParams([]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        $payload = $result->getPayload()->toJson();

        self::assertNotEquals(ResponseDirection::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.trans.uni.transfer', $payload);
        self::assertStringContainsString('DIRECT_TRANSFER', $payload);
        self::assertStringContainsString('TRANS_ACCOUNT_NO_PWD', $payload);
    }
}
