<?php

namespace Yansongda\Pay\Tests\Plugin\Alipay\Fund;

use PHPUnit\Framework\TestCase;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Plugin\Alipay\Fund\AccountQueryPlugin;
use Yansongda\Pay\Rocket;

class AccountQueryPluginTest extends TestCase
{
    public function testNormal()
    {
        $rocket = new Rocket();
        $rocket->setParams([]);

        $plugin = new AccountQueryPlugin();

        $result = $plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertNotEquals(ResponseParser::class, $result->getDirection());
        self::assertStringContainsString('alipay.fund.account.query', $result->getPayload()->toJson());
        self::assertStringContainsString('TRANS_ACCOUNT_NO_PWD', $result->getPayload()->toJson());
    }
}
